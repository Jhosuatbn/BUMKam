<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use App\Services\MonitoringKreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class MonitoringKreditController extends Controller
{
    public function index(Request $request, MonitoringKreditService $monitoring)
    {
        $perPage = 15;
        $page = Paginator::resolveCurrentPage();
        $offset = ($page - 1) * $perPage;
        $kataKunci = trim((string) $request->query('q', ''));
        $kataKunciNormal = mb_strtolower($kataKunci);
        $statusFilter = (string) $request->query('status', '');
        $ringkasan = [
            'pelanggan_aktif' => 0,
            'kredit_lunas' => 0,
            'kredit_belum_lunas' => 0,
            'jatuh_tempo_hari_ini' => 0,
            'angsuran_terlambat' => 0,
            'total_piutang' => 0.0,
        ];
        $pelangganAktif = [];
        $pageItems = collect();
        $jumlahCocok = 0;

        $this->queryKreditAktif()->with(array_merge([
            'pelanggan',
            'detailPemesanan.barang',
            'pembayaran' => fn ($query) => $query
                ->whereIn('status_gateway', ['settlement', 'partial_refund'])
                ->when(Schema::hasTable('alokasi_pembayaran'), fn ($query) => $query->with('alokasiDenda'))
                ->orderBy('tanggal_pembayaran')
                ->orderBy('id_pembayaran'),
        ], $this->relasiDendaTersedia()))
            ->orderByDesc('tanggal_pemesanan')
            ->orderByDesc('created_at')
            ->orderByDesc('id_pemesanan')
            ->chunk(100, function ($pemesanan) use (
                $monitoring,
                $kataKunciNormal,
                $statusFilter,
                $offset,
                $perPage,
                &$ringkasan,
                &$pelangganAktif,
                &$pageItems,
                &$jumlahCocok
            ) {
                foreach ($pemesanan as $item) {
                    $dataMonitoring = $monitoring->hitung($item);
                    $item->setAttribute('monitoring_kredit', $dataMonitoring);
                    $sisaKewajiban = (float) $dataMonitoring['sisa_kewajiban'];

                    if ($sisaKewajiban > 0) {
                        $pelangganAktif[$item->id_pelanggan] = true;
                        $ringkasan['kredit_belum_lunas']++;
                    }
                    if ($dataMonitoring['status'] === 'Lunas') {
                        $ringkasan['kredit_lunas']++;
                    }
                    $ringkasan['jatuh_tempo_hari_ini'] += (int) $dataMonitoring['angsuran_jatuh_tempo_hari_ini'];
                    $ringkasan['angsuran_terlambat'] += (int) $dataMonitoring['angsuran_terlambat'];
                    $ringkasan['total_piutang'] += $sisaKewajiban;

                    $cocokDenganPencarian = $kataKunciNormal === ''
                        || str_contains(mb_strtolower($item->id_pemesanan), $kataKunciNormal)
                        || str_contains(mb_strtolower((string) $item->pelanggan?->nama_pelanggan), $kataKunciNormal);
                    $cocokDenganStatus = $statusFilter === '' || $dataMonitoring['status'] === $statusFilter;

                    if ($cocokDenganPencarian && $cocokDenganStatus) {
                        if ($jumlahCocok >= $offset && $jumlahCocok < $offset + $perPage) {
                            $pageItems->push($item);
                        }
                        $jumlahCocok++;
                    }
                }
            });

        $ringkasan['pelanggan_aktif'] = count($pelangganAktif);
        $paginator = new Paginator(
            $pageItems,
            $jumlahCocok,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('monitoring-kredit.index', [
            'kredit' => $paginator,
            'ringkasan' => $ringkasan,
            'kataKunci' => $kataKunci,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function show(Pemesanan $pemesanan, MonitoringKreditService $monitoring)
    {
        abort_unless($this->dapatDimonitor($pemesanan), 404);

        $pemesanan->load(array_merge([
            'pelanggan',
            'detailPemesanan.barang',
            'pembayaran' => fn ($query) => $query
                ->whereIn('status_gateway', ['settlement', 'partial_refund'])
                ->when(Schema::hasTable('alokasi_pembayaran'), fn ($query) => $query->with('alokasiDenda'))
                ->orderBy('tanggal_pembayaran')
                ->orderBy('id_pembayaran'),
        ], $this->relasiDendaTersedia()));
        $ringkasan = $monitoring->hitung($pemesanan);

        return view('monitoring-kredit.show', compact('pemesanan', 'ringkasan'));
    }

    public function jadwal(MonitoringKreditService $monitoring)
    {
        $orders = $this->queryKreditAktif()->with(array_merge([
            'pelanggan',
            'pembayaran' => fn ($query) => $query->whereIn('status_gateway', ['settlement', 'partial_refund'])->when(Schema::hasTable('alokasi_pembayaran'), fn ($query) => $query->with('alokasiDenda'))->orderBy('tanggal_pembayaran')->orderBy('id_pembayaran'),
        ], $this->relasiDendaTersedia()))->orderByDesc('tanggal_pemesanan')->get();

        $jadwal = $orders->flatMap(function (Pemesanan $order) use ($monitoring) {
            return collect($monitoring->hitung($order)['jadwal'])->map(function (array $row) use ($order) {
                $row['pemesanan'] = $order;
                return $row;
            });
        });

        $dendaAktif = app(\App\Services\DendaKeterlambatanService::class)->tersedia();
        return view('monitoring-kredit.jadwal', compact('jadwal', 'dendaAktif'));
    }

    public function denda(Request $request, MonitoringKreditService $monitoring)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'dari_tanggal' => ['nullable', 'date'],
            'sampai_tanggal' => ['nullable', 'date', 'after_or_equal:dari_tanggal'],
        ]);
        $kataKunci = trim((string) $request->query('q', ''));
        $dariTanggal = $request->query('dari_tanggal');
        $sampaiTanggal = $request->query('sampai_tanggal');
        $perPage = 15;
        $page = Paginator::resolveCurrentPage();
        $orders = $this->queryKreditAktif()
            ->when($kataKunci !== '', fn ($query) => $query->whereHas('pelanggan', fn ($pelanggan) => $pelanggan->where('nama_pelanggan', 'like', '%'.$kataKunci.'%')))
            ->with(array_merge([
                'pelanggan',
                'pembayaran' => fn ($query) => $query->whereIn('status_gateway', ['settlement', 'partial_refund'])->when(Schema::hasTable('alokasi_pembayaran'), fn ($query) => $query->with('alokasiDenda'))->orderBy('tanggal_pembayaran')->orderBy('id_pembayaran'),
            ], $this->relasiDendaTersedia()))
            ->orderByDesc('tanggal_pemesanan')
            ->get();

        $barisDenda = collect();
        foreach ($orders as $order) {
            foreach ($monitoring->hitung($order)['jadwal'] as $row) {
                $tanggalJatuhTempo = $row['tanggal_jatuh_tempo']->toDateString();
                $sesuaiRentangTanggal = (! $dariTanggal || $tanggalJatuhTempo >= $dariTanggal)
                    && (! $sampaiTanggal || $tanggalJatuhTempo <= $sampaiTanggal);
                if ($row['denda_awal'] > 0 && $sesuaiRentangTanggal) {
                    $barisDenda->push(['pemesanan' => $order, 'angsuran' => $row]);
                }
            }
        }

        $dataDenda = new Paginator(
            $barisDenda->forPage($page, $perPage)->values(),
            $barisDenda->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $dendaAktif = app(\App\Services\DendaKeterlambatanService::class)->tersedia();
        return view('monitoring-kredit.denda', compact('dataDenda', 'dendaAktif', 'kataKunci', 'dariTanggal', 'sampaiTanggal'));
    }

    private function relasiDendaTersedia(bool $withPimpinan = false): array
    {
        $relations = [];
        if (Schema::hasTable('penyesuaian_denda')) {
            $relations[] = $withPimpinan ? 'penyesuaianDenda.pimpinan' : 'penyesuaianDenda';
        }
        if (Schema::hasTable('denda_angsuran')) $relations[] = 'dendaAngsuran';
        return $relations;
    }

    private function queryKreditAktif()
    {
        return Pemesanan::query()
            ->where('tenor', '>', 0)
            ->where(fn ($query) => $query
                ->whereNull('status_persetujuan')
                ->orWhere('status_persetujuan', 'Disetujui'));
    }

    private function dapatDimonitor(Pemesanan $pemesanan): bool
    {
        return (int) $pemesanan->tenor > 0
            && ($pemesanan->status_persetujuan === null || $pemesanan->status_persetujuan === 'Disetujui');
    }
}
