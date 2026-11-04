<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailRetur;
use App\Models\Pemesanan;
use App\Models\ReturPelanggan;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReturPelangganController extends Controller
{
    public function index()
    {
        $rows = ReturPelanggan::with(['pemesanan.pelanggan', 'pencatat', 'pemutus'])
            ->latest()->paginate(20)->withQueryString();
        return view('retur.index', compact('rows'));
    }

    public function create()
    {
        $this->staffOnly();
        $orders = Pemesanan::with(['pelanggan', 'detailPemesanan.barang', 'returPelanggan.details'])
            ->whereIn('status_penyerahan', ['Diserahkan', 'Riwayat Lama'])
            ->orderByDesc('tanggal_pemesanan')->get();
        return view('retur.create', compact('orders'));
    }

    public function store(Request $request)
    {
        $this->staffOnly();
        $data = $request->validate([
            'id_pemesanan' => ['required', 'exists:pemesanan,id_pemesanan'],
            'alasan' => ['required', 'string', 'min:5', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_detail' => ['required', 'exists:detail_pemesanan,id_detail', 'distinct'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.kondisi_barang' => ['required', 'in:Layak Dijual,Rusak,Perlu Pemeriksaan'],
            'items.*.keterangan' => ['nullable', 'string', 'max:1000'],
        ]);
        $id = 'RTR-' . Str::upper(Str::random(20));

        DB::transaction(function () use ($data, $request, $id) {
            $order = Pemesanan::whereKey($data['id_pemesanan'])->lockForUpdate()->firstOrFail();
            if (! in_array($order->status_penyerahan, ['Diserahkan', 'Riwayat Lama'], true)) {
                throw ValidationException::withMessages(['id_pemesanan' => 'Retur hanya dapat diajukan untuk barang yang sudah diserahkan.']);
            }
            $header = ReturPelanggan::create([
                'id_retur' => $id,
                'id_pemesanan' => $order->id_pemesanan,
                'dicatat_oleh' => $request->user()->id_pengguna,
                'status' => 'Menunggu Persetujuan',
                'alasan' => $data['alasan'],
            ]);

            foreach ($data['items'] as $input) {
                $detail = $order->detailPemesanan()->whereKey($input['id_detail'])->first();
                if (! $detail) throw ValidationException::withMessages(['items' => 'Barang retur tidak cocok dengan pemesanan yang dipilih.']);
                $reservedQty = (int) DetailRetur::where('id_detail_pemesanan', $detail->id_detail)
                    ->whereHas('retur', fn ($query) => $query->whereIn('status', ['Menunggu Persetujuan', 'Disetujui']))
                    ->sum('jumlah');
                if ((int) $input['jumlah'] > max(0, (int) $detail->jumlah - $reservedQty)) {
                    throw ValidationException::withMessages(['items' => 'Jumlah retur melebihi barang yang belum pernah diajukan retur.']);
                }
                DetailRetur::create([
                    'id_detail_retur' => 'DRT-' . Str::upper(Str::random(20)),
                    'id_retur' => $header->id_retur,
                    'id_detail_pemesanan' => $detail->id_detail,
                    'id_barang' => $detail->id_barang,
                    'jumlah' => $input['jumlah'],
                    'kondisi_barang' => $input['kondisi_barang'],
                    'keterangan' => $input['keterangan'] ?? null,
                ]);
            }
        });

        return redirect()->route('retur.index')->with('success', 'Pengajuan retur tersimpan dan menunggu keputusan Pimpinan.');
    }

    public function show(ReturPelanggan $retur)
    {
        $retur->load(['pemesanan.pelanggan', 'details.barang', 'details.detailPemesanan', 'pencatat', 'pemutus']);
        return view('retur.show', compact('retur'));
    }

    public function decide(Request $request, ReturPelanggan $retur, StockMovementService $movements)
    {
        abort_unless($request->user()?->peran === 'Pimpinan', 403);
        $data = $request->validate([
            'keputusan' => ['required', 'in:Disetujui,Ditolak'],
            'catatan_keputusan' => ['required', 'string', 'min:5', 'max:2000'],
            'masuk_stok' => ['nullable', 'array'],
            'masuk_stok.*' => ['boolean'],
        ]);

        DB::transaction(function () use ($request, $retur, $data, $movements) {
            $header = ReturPelanggan::whereKey($retur->id_retur)->lockForUpdate()->with('details')->firstOrFail();
            if ($header->status !== 'Menunggu Persetujuan') throw ValidationException::withMessages(['keputusan' => 'Pengajuan retur ini sudah diputuskan.']);
            if ($data['keputusan'] === 'Disetujui') {
                foreach ($header->details as $detail) {
                    $masukStok = (bool) ($data['masuk_stok'][$detail->id_detail_retur] ?? false);
                    if ($masukStok && $detail->kondisi_barang !== 'Layak Dijual') {
                        throw ValidationException::withMessages(['masuk_stok' => 'Barang rusak atau belum diperiksa tidak boleh dimasukkan ke stok.']);
                    }
                    $barang = Barang::whereKey($detail->id_barang)->lockForUpdate()->firstOrFail();
                    if ($masukStok) {
                        if ($barang->stok === null) throw ValidationException::withMessages(['masuk_stok' => 'Atur stok barang sebelum mengembalikan barang ini ke persediaan.']);
                        $movements->apply($barang, (int) $detail->jumlah, $request->user()->id_pengguna, 'Retur Pelanggan', 'ReturPelanggan', $header->id_retur, 'Barang layak jual, keputusan: ' . $data['catatan_keputusan']);
                    } elseif ($barang->stok !== null) {
                        $movements->apply($barang, 0, $request->user()->id_pengguna, 'Retur Tidak Masuk Stok', 'ReturPelanggan', $header->id_retur, $detail->kondisi_barang . '; ' . $data['catatan_keputusan']);
                    }
                    $detail->update(['masuk_stok' => $masukStok]);
                }
            }
            $header->update([
                'status' => $data['keputusan'],
                'diputuskan_oleh' => $request->user()->id_pengguna,
                'diputuskan_pada' => now(),
                'catatan_keputusan' => $data['catatan_keputusan'],
            ]);
        });

        return back()->with('success', 'Keputusan retur tersimpan dan pergerakan stok tercatat.');
    }

    private function staffOnly(): void
    {
        abort_unless(auth()->user()?->peran === 'Staff', 403);
    }
}
