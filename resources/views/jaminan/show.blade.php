@extends('layouts.sneat-dashboard')
@section('title', 'Detail Dokumen Jaminan')
@section('content')
<div class="space-y-6">
    @if (session('success'))<div role="status" class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div role="alert" class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <section class="card"><div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3"><div><h2 class="card-title mb-1">{{ $jaminan->jenis_dokumen }} · {{ $jaminan->id_jaminan }}</h2><p class="mb-0 text-body-secondary">{{ $jaminan->id_pemesanan }} · {{ $jaminan->pemesanan?->pelanggan?->nama_pelanggan }}</p></div><a class="btn btn-outline-secondary" href="{{ route('jaminan.index') }}"><i class="icon-base bx bx-arrow-back me-1" aria-hidden="true"></i>Kembali</a></div>
        <div class="card-body grid grid-cols-1 gap-5 lg:grid-cols-2">
            <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2"><div><dt class="text-body-secondary">Nomor dokumen</dt><dd class="fw-semibold">{{ $jaminan->nomor_dokumen }}</dd></div><div><dt class="text-body-secondary">Nama pemilik</dt><dd class="fw-semibold">{{ $jaminan->nama_pemilik }}</dd></div><div><dt class="text-body-secondary">Tanggal penyerahan</dt><dd class="fw-semibold">{{ $jaminan->tanggal_penyerahan->format('d/m/Y') }}</dd></div><div><dt class="text-body-secondary">Status pengembalian</dt><dd class="fw-semibold">{{ $jaminan->status_pengembalian }}</dd></div><div class="sm:col-span-2"><dt class="text-body-secondary">Catatan</dt><dd class="fw-semibold">{{ $jaminan->catatan ?: '—' }}</dd></div></dl>
            <div><h3 class="h6">Dokumen privat</h3><iframe title="Pratinjau dokumen jaminan" src="{{ route('jaminan.document', $jaminan) }}" class="w-full rounded border" style="height: 28rem"></iframe><a class="btn btn-sm btn-outline-primary mt-2" href="{{ route('jaminan.document', $jaminan) }}" target="_blank" rel="noopener">Buka dokumen</a></div>
        </div>
        @if (auth()->user()->peran === 'Pimpinan')
            <div class="card-footer"><form method="POST" action="{{ route('jaminan.verify', $jaminan) }}" class="grid grid-cols-1 gap-3 sm:grid-cols-[14rem_1fr_auto]">@csrf @method('PUT')<select class="form-select" name="status_verifikasi" required><option value="Terverifikasi">Terverifikasi</option><option value="Ditolak">Ditolak</option></select><input class="form-control" name="alasan_penolakan" maxlength="2000" placeholder="Alasan jika dokumen ditolak" value="{{ old('alasan_penolakan') }}"><button class="btn btn-primary" type="submit">Simpan Verifikasi</button></form></div>
        @else
            <div class="card-footer space-y-4">
                <form method="POST" enctype="multipart/form-data" action="{{ route('jaminan.update', $jaminan) }}" class="grid grid-cols-1 gap-3 md:grid-cols-2">@csrf @method('PUT')<select class="form-select" name="jenis_dokumen" required><option @selected($jaminan->jenis_dokumen === 'BPKB')>BPKB</option><option @selected($jaminan->jenis_dokumen === 'Surat Tanah')>Surat Tanah</option></select><input class="form-control" name="nomor_dokumen" required maxlength="100" value="{{ $jaminan->nomor_dokumen }}"><input class="form-control" name="nama_pemilik" required maxlength="100" value="{{ $jaminan->nama_pemilik }}"><input class="form-control" type="date" name="tanggal_penyerahan" required value="{{ $jaminan->tanggal_penyerahan->toDateString() }}"><input class="form-control" type="file" name="file_dokumen" accept="image/jpeg,image/png,image/webp,application/pdf"><textarea class="form-control" name="catatan" maxlength="2000" placeholder="Catatan">{{ $jaminan->catatan }}</textarea><button class="btn btn-primary md:col-span-2" type="submit">Perbarui Data Jaminan</button></form>
                @if ($jaminan->status_pengembalian !== 'Sudah Dikembalikan' && $jaminan->pemesanan?->status_pemesanan === 'Lunas')<form method="POST" action="{{ route('jaminan.return', $jaminan) }}">@csrf @method('PUT')<input type="hidden" name="status_pengembalian" value="Sudah Dikembalikan"><button class="btn btn-outline-success" type="submit">Tandai Sudah Dikembalikan</button></form>@endif
            </div>
        @endif
    </section>
</div>
@endsection
