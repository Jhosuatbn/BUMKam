@extends('layouts.sneat-dashboard')

@section('title', 'Detail Pemesanan')

@section('content')
    <div class="pemesanan-page space-y-6">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900" aria-live="assertive">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="pemesanan-detail-heading">
            <h2 id="pemesanan-detail-heading" class="border-b border-slate-200 px-5 py-4 text-base font-semibold text-slate-900 sm:px-6">Informasi Pemesanan</h2>
            <dl class="grid grid-cols-1 gap-x-10 px-5 sm:grid-cols-2 sm:px-6">
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">ID Pemesanan</dt><dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pemesanan->id_pemesanan }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Pelanggan</dt><dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pemesanan->pelanggan->nama_pelanggan ?? '-' }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Tanggal</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pemesanan->tanggal_pemesanan }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Skema Pembayaran</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pemesanan->skema_pembayaran }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">DP</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pemesanan->dp, 0, ',', '.') }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Tenor</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pemesanan->tenor }} bulan</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Keterangan Jaminan</dt><dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $pemesanan->jaminanDokumen?->catatan ?? $pemesanan->jaminan ?? '—' }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Bunga</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pemesanan->bunga, 0, ',', '.') }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Total Harga</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Angsuran</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($pemesanan->angsuran, 0, ',', '.') }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Status Persetujuan</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pemesanan->status_persetujuan ?? 'Data lama — tidak memerlukan approval' }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Status Pembayaran</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pemesanan->status_pemesanan }}</dd></div>
                <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Status Penyerahan</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pemesanan->status_penyerahan === 'Riwayat Lama' ? 'Riwayat stok sebelum pencatatan penyerahan diterapkan' : $pemesanan->status_penyerahan }}</dd></div>
                @if ($pemesanan->diserahkan_pada)
                    <div class="border-b border-slate-100 py-4"><dt class="text-sm font-medium text-slate-500">Diserahkan oleh</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pemesanan->diserahkanOleh?->nama_lengkap ?? $pemesanan->diserahkanOleh?->username ?? 'Pengguna' }} · {{ $pemesanan->diserahkan_pada->locale('id')->translatedFormat('d M Y, H:i') }}</dd></div>
                @endif
                @if ($pemesanan->status_persetujuan === 'Dibatalkan')
                    <div class="border-b border-slate-100 py-4 sm:col-span-2"><dt class="text-sm font-medium text-slate-500">Alasan Pembatalan</dt><dd class="mt-1 whitespace-pre-line text-sm text-slate-900">{{ $pemesanan->alasan_pembatalan }}</dd></div>
                @endif
                @if ($pemesanan->status_persetujuan === 'Ditolak')
                    <div class="border-b border-slate-100 py-4 sm:col-span-2"><dt class="text-sm font-medium text-slate-500">Alasan Penolakan</dt><dd class="mt-1 whitespace-pre-line text-sm text-rose-800">{{ $pemesanan->catatan_persetujuan }}</dd></div>
                @elseif ($pemesanan->persetujuan_pada)
                    <div class="border-b border-slate-100 py-4 sm:col-span-2"><dt class="text-sm font-medium text-slate-500">Diputuskan oleh</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pemesanan->persetujuanOleh?->nama_lengkap ?? $pemesanan->persetujuanOleh?->username ?? 'Pimpinan' }} · {{ $pemesanan->persetujuan_pada->locale('id')->translatedFormat('d M Y, H:i') }}</dd></div>
                @endif
            </dl>
        </section>

        @if ($pemesanan->status_persetujuan !== null)
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="approval-heading">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6"><h2 id="approval-heading" class="text-base font-semibold text-slate-900">Persetujuan Pemesanan</h2><p class="mt-1 text-sm text-slate-500">Status, keputusan, dan riwayat pemeriksaan pengajuan.</p></div>
                @if ($pemesanan->jaminanDokumen)
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <h3 class="text-sm font-semibold text-slate-800">Dokumen Jaminan</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ $pemesanan->jaminanDokumen->jenis_dokumen === 'Surat Tanah' ? 'Sertifikat Tanah' : $pemesanan->jaminanDokumen->jenis_dokumen }} · No. {{ $pemesanan->jaminanDokumen->nomor_dokumen }} · Diterima oleh {{ $pemesanan->jaminanDokumen->penerima?->nama_lengkap ?? $pemesanan->jaminanDokumen->penerima?->username ?? 'belum tercatat' }}</p>
                        <p class="mt-1 text-xs text-slate-500">Verifikasi: {{ $pemesanan->jaminanDokumen->status_verifikasi_label }} · Pengembalian: {{ $pemesanan->jaminanDokumen->status_pengembalian_label }}</p>
                        @if ($pemesanan->jaminanDokumen->alasan_penolakan)
                            <p class="mt-1 text-xs text-rose-700">Alasan penolakan: {{ $pemesanan->jaminanDokumen->alasan_penolakan }}</p>
                        @endif
                        @if ($pemesanan->jaminanDokumen->file_path)
                            <a href="{{ route('jaminan.document', $pemesanan->jaminanDokumen) }}" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-900"><i class="icon-base bx bx-file" aria-hidden="true"></i>Lihat dokumen lama</a>
                        @else
                            <p class="mt-2 text-xs text-slate-500">Salinan fisik jaminan tidak disimpan di sistem.</p>
                        @endif
                    </div>
                @else
                    <p class="border-b border-slate-100 px-5 py-4 text-sm text-slate-500 sm:px-6">Dokumen jaminan belum diunggah.</p>
                @endif

                @if (auth()->user()?->peran === 'Pimpinan' && $pemesanan->status_persetujuan === 'Menunggu Persetujuan')
                    <form action="{{ route('pemesanan.approve', $pemesanan) }}" method="POST" class="border-b border-slate-100 p-5 sm:px-6">
                        @csrf @method('PUT')
                        <label for="catatan_persetujuan" class="block text-sm font-semibold text-slate-700">Catatan atau alasan penolakan</label>
                        <textarea id="catatan_persetujuan" name="catatan_persetujuan" rows="3" maxlength="2000" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" placeholder="Wajib diisi jika pengajuan ditolak">{{ old('catatan_persetujuan') }}</textarea>
                        @error('status_persetujuan')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        @error('catatan_persetujuan')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        <div class="mt-4 flex flex-wrap gap-3">
                            <button type="submit" name="status_persetujuan" value="Disetujui" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800"><i class="icon-base bx bx-check" aria-hidden="true"></i>Setujui Pemesanan</button>
                            <button type="submit" name="status_persetujuan" value="Ditolak" formnovalidate class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-rose-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-800" onclick="if (!this.form.elements.catatan_persetujuan.value.trim()) { this.form.elements.catatan_persetujuan.setCustomValidity('Alasan penolakan wajib diisi.'); this.form.elements.catatan_persetujuan.reportValidity(); this.form.elements.catatan_persetujuan.setCustomValidity(''); return false; }"><i class="icon-base bx bx-x" aria-hidden="true"></i>Tolak Pemesanan</button>
                        </div>
                    </form>
                @endif

                @if (auth()->user()?->peran === 'Staff' && $pemesanan->status_persetujuan === 'Disetujui' && $pemesanan->status_penyerahan === 'Belum Diserahkan')
                    <div class="border-b border-slate-100 p-5 sm:px-6">
                        <form action="{{ route('pemesanan.handover', $pemesanan) }}" method="POST" onsubmit="return confirm('Catat penyerahan barang dan kurangi stok fisik sekarang?')">
                            @csrf
                            <button type="submit" class="bumkam-green-action inline-flex min-h-10 items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm">Catat Penyerahan Barang</button>
                        </form>
                    </div>
                @endif

                @if (auth()->user()?->peran === 'Staff' && in_array($pemesanan->status_persetujuan, ['Menunggu Persetujuan', 'Disetujui'], true) && $pemesanan->status_penyerahan === 'Belum Diserahkan' && ! $pemesanan->pembayaran->isNotEmpty())
                    <div class="border-b border-slate-100 p-5 sm:px-6">
                        <form action="{{ route('pemesanan.cancel', $pemesanan) }}" method="POST" data-confirm-delete="Batalkan pemesanan ini? Reservasi stok akan dilepas.">
                            @csrf
                            <label for="alasan_pembatalan" class="mb-2 block text-sm font-semibold text-slate-700">Alasan pembatalan</label>
                            <textarea id="alasan_pembatalan" name="alasan_pembatalan" required maxlength="2000" rows="2" class="mb-3 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Jelaskan alasan pembatalan">{{ old('alasan_pembatalan') }}</textarea>
                            <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">Batalkan Pemesanan</button>
                        </form>
                    </div>
                @endif

                <div class="divide-y divide-slate-100 px-5 sm:px-6"><h3 class="py-4 text-sm font-semibold text-slate-800">Riwayat Persetujuan</h3>
                    @forelse ($pemesanan->riwayatPersetujuan as $riwayat)
                        <div class="flex flex-col justify-between gap-1 py-3 sm:flex-row sm:items-start"><div><p class="text-sm font-semibold text-slate-800">{{ $riwayat->status_persetujuan }} <span class="font-normal text-slate-500">oleh {{ $riwayat->pengguna?->nama_lengkap ?? $riwayat->pengguna?->username ?? 'Pengguna' }}</span></p>@if ($riwayat->catatan)<p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ $riwayat->catatan }}</p>@endif</div><time class="text-xs text-slate-500">{{ $riwayat->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}</time></div>
                    @empty
                        <p class="pb-4 text-sm text-slate-500">Belum ada riwayat keputusan.</p>
                    @endforelse
                </div>
            </section>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="barang-dipesan-heading">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6"><h2 id="barang-dipesan-heading" class="text-base font-semibold text-slate-900">Barang yang Dipesan</h2></div>
            <div class="divide-y divide-slate-100 px-5 sm:px-6">
                @forelse ($pemesanan->detailPemesanan as $detail)
                    <dl class="grid grid-cols-1 gap-x-8 py-4 sm:grid-cols-2">
                        <div class="py-2"><dt class="text-sm font-medium text-slate-500">Barang</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $detail->barang->nama_barang ?? '-' }}</dd></div>
                        <div class="py-2"><dt class="text-sm font-medium text-slate-500">Harga</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($detail->harga, 0, ',', '.') }}</dd></div>
                        <div class="py-2"><dt class="text-sm font-medium text-slate-500">Jumlah</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $detail->jumlah }}</dd></div>
                        <div class="py-2"><dt class="text-sm font-medium text-slate-500">Subtotal</dt><dd class="mt-1 tabular-nums text-sm font-semibold text-slate-900">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</dd></div>
                    </dl>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">Belum ada detail barang.</p>
                @endforelse
            </div>
            <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 px-5 py-4 sm:px-6">
                @if (auth()->user()?->peran !== 'Pimpinan' && ($pemesanan->status_persetujuan === null || $pemesanan->status_persetujuan === 'Menunggu Persetujuan'))
                    <a href="{{ route('pemesanan.edit', $pemesanan->id_pemesanan) }}" class="bumkam-green-action inline-flex min-h-10 items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold shadow-sm transition">Edit</a>
                @endif
                <a href="{{ route('pemesanan.index') }}" class="btn bumkam-green-action"><i class="icon-base bx bx-arrow-back" aria-hidden="true"></i>Kembali</a>
            </div>
        </section>
    </div>
@endsection
