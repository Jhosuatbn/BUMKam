<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use App\Services\MonitoringKreditService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class MonitoringKreditController extends Controller
{
    public function index(Request $request, MonitoringKreditService $monitoring)
    {
        $pemesanan = Pemesanan::with([
            'pelanggan',
            'detailPemesanan.barang',
            'pembayaran' => fn ($query) => $query
                ->where('status_gateway', 'settlement')
                ->orderBy('tanggal_pembayaran')
                ->orderBy('id_pembayaran'),
        ])->where('tenor', '>', 0)->orderByDesc('tanggal_pemesanan')->get();

        $kredit = $pemesanan->map(function (Pemesanan $item) use ($monitoring) {
            $item->setAttribute('monitoring_kredit', $monitoring->hitung($item));

            return $item;
        });

        $ringkasan = [
            'pelanggan_aktif' => $kredit->where('monitoring_kredit.sisa_kewajiban', '>', 0)
                ->pluck('id_pelanggan')->unique()->count(),
            'kredit_lunas' => $kredit->where('monitoring_kredit.status', 'Lunas')->count(),
            'kredit_belum_lunas' => $kredit->where('monitoring_kredit.sisa_kewajiban', '>', 0)->count(),
            'jatuh_tempo_hari_ini' => $kredit->sum('monitoring_kredit.angsuran_jatuh_tempo_hari_ini'),
            'angsuran_terlambat' => $kredit->sum('monitoring_kredit.angsuran_terlambat'),
            'total_piutang' => $kredit->sum('monitoring_kredit.sisa_kewajiban'),
        ];

        $kataKunci = trim((string) $request->query('q', ''));
        $statusFilter = (string) $request->query('status', '');
        $kredit = $kredit
            ->when($kataKunci !== '', fn ($items) => $items->filter(fn (Pemesanan $item) =>
                str_contains(mb_strtolower($item->id_pemesanan), mb_strtolower($kataKunci))
                || str_contains(mb_strtolower((string) $item->pelanggan?->nama_pelanggan), mb_strtolower($kataKunci))
            ))
            ->when($statusFilter !== '', fn ($items) => $items->filter(fn (Pemesanan $item) =>
                $item->monitoring_kredit['status'] === $statusFilter
            ))
            ->values();

        $perPage = 15;
        $page = Paginator::resolveCurrentPage();
        $paginator = new Paginator(
            $kredit->forPage($page, $perPage)->values(),
            $kredit->count(),
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
        abort_if((int) $pemesanan->tenor <= 0, 404);

        $pemesanan->load([
            'pelanggan',
            'detailPemesanan.barang',
            'pembayaran' => fn ($query) => $query
                ->where('status_gateway', 'settlement')
                ->orderBy('tanggal_pembayaran')
                ->orderBy('id_pembayaran'),
        ]);
        $ringkasan = $monitoring->hitung($pemesanan);

        return view('monitoring-kredit.show', compact('pemesanan', 'ringkasan'));
    }
}
