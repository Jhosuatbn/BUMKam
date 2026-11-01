@extends('layouts.sneat-dashboard')

@section('title', 'Manajemen Dokumen Jaminan')

@section('content')
<div class="space-y-6">
    @if (session('success'))<div role="status" class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div role="alert" class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if (auth()->user()->peran !== 'Pimpinan')
        <section class="card">
            <div class="card-header"><h2 class="card-title mb-1">Unggah Dokumen Jaminan</h2><p class="mb-0 text-body-secondary">Dokumen disimpan secara privat dan hanya bisa dibuka setelah login.</p></div>
            <div class="card-body">
                <form action="{{ route('jaminan.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @csrf
                    <div><label class="form-label" for="id_pemesanan">Pemesanan kredit</label><select class="form-select" id="id_pemesanan" name="id_pemesanan" required><option value="">Pilih pemesanan</option>@foreach ($pemesananTersedia as $order)<option value="{{ $order->id_pemesanan }}" @selected(old('id_pemesanan') === $order->id_pemesanan)>{{ $order->id_pemesanan }} · {{ $order->pelanggan?->nama_pelanggan }}</option>@endforeach</select></div>
                    <div><label class="form-label" for="jenis_dokumen">Jenis jaminan</label><select class="form-select" id="jenis_dokumen" name="jenis_dokumen" required><option value="">Pilih jenis</option><option>BPKB</option><option>Surat Tanah</option></select></div>
                    <div><label class="form-label" for="nomor_dokumen">Nomor dokumen</label><input class="form-control" id="nomor_dokumen" name="nomor_dokumen" maxlength="100" required value="{{ old('nomor_dokumen') }}"></div>
                    <div><label class="form-label" for="nama_pemilik">Nama pemilik dokumen</label><input class="form-control" id="nama_pemilik" name="nama_pemilik" maxlength="100" required value="{{ old('nama_pemilik') }}"></div>
                    <div><label class="form-label" for="file_dokumen">Foto / scan dokumen (JPG, PNG, WEBP, PDF; maks. 5 MB)</label><input class="form-control" id="file_dokumen" name="file_dokumen" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" required></div>
                    <div><label class="form-label" for="tanggal_penyerahan">Tanggal penyerahan</label><input class="form-control" id="tanggal_penyerahan" name="tanggal_penyerahan" type="date" required value="{{ old('tanggal_penyerahan', now()->toDateString()) }}"></div>
                    <div class="md:col-span-2"><label class="form-label" for="catatan">Catatan tambahan</label><textarea class="form-control" id="catatan" name="catatan" rows="2" maxlength="2000">{{ old('catatan') }}</textarea></div>
                    <div class="md:col-span-2"><button class="btn btn-primary" type="submit"><i class="icon-base bx bx-upload me-1" aria-hidden="true"></i>Simpan Dokumen</button></div>
                </form>
            </div>
        </section>
    @endif

    <section class="card overflow-hidden">
        <div class="card-header"><h2 class="card-title mb-1">Manajemen Dokumen Jaminan</h2><p class="mb-0 text-body-secondary">Data jaminan, pemeriksaan Pimpinan, dan status pengembalian.</p></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>ID / Pemesanan</th><th>Pelanggan</th><th>Jenis / Pemilik</th><th>Verifikasi</th><th>Pengembalian</th><th>Aksi</th></tr></thead><tbody>
            @forelse ($jaminan as $item)
                <tr><td class="text-nowrap"><strong>{{ $item->id_jaminan }}</strong><small class="d-block text-body-secondary">{{ $item->id_pemesanan }}</small></td><td>{{ $item->pemesanan?->pelanggan?->nama_pelanggan ?? '—' }}</td><td>{{ $item->jenis_dokumen }}<small class="d-block text-body-secondary">{{ $item->nama_pemilik }}</small></td><td><span class="badge bg-label-{{ $item->status_verifikasi === 'Terverifikasi' ? 'success' : ($item->status_verifikasi === 'Ditolak' ? 'danger' : 'warning') }}">{{ $item->status_verifikasi }}</span>@if ($item->alasan_penolakan)<small class="d-block text-danger">{{ $item->alasan_penolakan }}</small>@endif</td><td>{{ $item->status_pengembalian }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('jaminan.show', $item) }}"><i class="icon-base bx bx-show-alt me-1" aria-hidden="true"></i>Kelola</a></td></tr>
            @empty<tr><td colspan="6" class="py-5 text-center text-body-secondary">Belum ada dokumen jaminan yang tercatat.</td></tr>@endforelse
        </tbody></table></div>
        @if ($jaminan->hasPages())<div class="card-footer">{{ $jaminan->links() }}</div>@endif
    </section>
</div>
@endsection
