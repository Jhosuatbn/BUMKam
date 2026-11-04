<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use App\Services\MonitoringKreditService;
use App\Models\PenyesuaianDenda;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

    public function denda(MonitoringKreditService $monitoring)
    {
        $orders = $this->queryKreditAktif()->with(array_merge([
            'pelanggan',
            'pembayaran' => fn ($query) => $query->whereIn('status_gateway', ['settlement', 'partial_refund'])->when(Schema::hasTable('alokasi_pembayaran'), fn ($query) => $query->with('alokasiDenda'))->orderBy('tanggal_pembayaran')->orderBy('id_pembayaran'),
        ], $this->relasiDendaTersedia(true)))->orderByDesc('tanggal_pemesanan')->get();

        $dataDenda = $orders->map(fn (Pemesanan $order) => [
            'pemesanan' => $order,
            'ringkasan' => $monitoring->hitung($order),
        ]);

        $dendaAktif = app(\App\Services\DendaKeterlambatanService::class)->tersedia();
        return view('monitoring-kredit.denda', compact('dataDenda', 'dendaAktif'));
    }

    public function kurangiDenda(Request $request, Pemesanan $pemesanan, MonitoringKreditService $monitoring)
    {
        abort_unless(app(\App\Services\DendaKeterlambatanService::class)->tersedia(), 503, 'Migration fitur denda belum diterapkan.');
        abort_unless($request->user()?->peran === 'Pimpinan', 403, 'Hanya Pimpinan yang dapat memutuskan pengurangan denda.');
        abort_unless($this->dapatDimonitor($pemesanan), 404);
        $validated = $request->validate([
            'nomor_angsuran' => ['required', 'integer', 'min:1', 'max:120'],
            'nominal_pengurangan' => ['required', 'numeric', 'gt:0'],
            'alasan' => ['required', 'string', 'min:8', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $pemesanan, $monitoring, $validated) {
            $order = Pemesanan::whereKey($pemesanan->getKey())->lockForUpdate()->with(array_merge([
                'pembayaran' => fn ($query) => $query->whereIn('status_gateway', ['settlement', 'partial_refund'])->with('alokasiDenda')->orderBy('tanggal_pembayaran')->orderBy('id_pembayaran'),
            ], $this->relasiDendaTersedia()))->firstOrFail();
            $row = collect($monitoring->hitung($order)['jadwal'])->firstWhere('nomor', (int) $validated['nomor_angsuran']);
            $pengurangan = round((float) $validated['nominal_pengurangan'], 2);
            if (! $row || $pengurangan > $row['denda_berjalan']) {
                throw ValidationException::withMessages(['nominal_pengurangan' => 'Pengurangan tidak boleh melebihi denda berjalan pada angsuran ini.']);
            }

            $record = PenyesuaianDenda::create([
                'id_pemesanan' => $order->id_pemesanan,
                'nomor_angsuran' => $row['nomor'],
                'denda_awal' => $row['denda_awal'],
                'nominal_pengurangan' => $pengurangan,
                'alasan' => $validated['alasan'],
                'id_pimpinan' => $request->user()->id_pengguna,
                'diputuskan_pada' => now(config('kredit.zona_waktu', 'Asia/Jakarta')),
            ]);
            AuditTrail::create([
                'id_pengguna' => $request->user()->id_pengguna,
                'nama_pengguna' => $request->user()->nama_lengkap,
                'peran_pengguna' => $request->user()->peran,
                'jenis_aktivitas' => 'penyesuaian_denda',
                'modul' => 'Monitoring Kredit',
                'deskripsi' => "Pimpinan menyetujui pengurangan denda angsuran {$row['nomor']} pada {$order->id_pemesanan}: Rp ".number_format($pengurangan, 2, ',', '.').". Alasan: ".$validated['alasan'],
                'ip_address' => $request->ip(),
                'old_value' => ['denda_awal' => $row['denda_awal']],
                'new_value' => ['nominal_pengurangan' => $record->nominal_pengurangan, 'alasan' => $record->alasan, 'id_penyesuaian_denda' => $record->id_penyesuaian_denda],
                'created_at' => now(config('kredit.zona_waktu', 'Asia/Jakarta')),
            ]);
        });

        return back()->with('success', 'Pengurangan denda berhasil dicatat dan masuk ke riwayat keputusan.');
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
