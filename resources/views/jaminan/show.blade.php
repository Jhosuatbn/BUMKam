@extends('layouts.sneat-dashboard')

@section('title', 'Detail Jaminan')

@section('content')
    @php
        $statusVerifikasiClass = match ($jaminan->status_verifikasi_label) {
            'Disetujui' => 'success',
            'Ditolak' => 'danger',
            default => 'warning',
        };
        $statusPengembalianClass = $jaminan->status_pengembalian === 'Sudah Dikembalikan' ? 'success' : 'secondary';
        $ekstensiDokumen = strtolower(pathinfo($jaminan->file_path, PATHINFO_EXTENSION));
        $dokumenGambar = in_array($ekstensiDokumen, ['jpg', 'jpeg', 'png', 'webp'], true);
    @endphp
    <div class="space-y-6">
        @if (session('success'))
            <div role="status" class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="alert alert-danger">
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="card overflow-hidden" aria-labelledby="jaminan-detail-heading">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3 border-b">
                <div>
                    <h1 id="jaminan-detail-heading" class="card-title mb-1">Detail Jaminan · {{ $jaminan->id_jaminan }}</h1>
                    <p class="mb-0 text-body-secondary">Pemesanan {{ $jaminan->id_pemesanan }} · {{ $jaminan->pemesanan->pelanggan?->nama_pelanggan ?? 'Pelanggan tidak ditemukan' }}</p>
                </div>
                <a class="btn btn-success" href="{{ route('jaminan.index') }}"><i class="icon-base bx bx-arrow-back me-1" aria-hidden="true"></i>Kembali</a>
            </div>

            <div class="card-body">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="border-b border-slate-200 pb-3"><dt class="mb-1 text-body-secondary">Pelanggan</dt><dd class="mb-0 fw-semibold">{{ $jaminan->pemesanan->pelanggan?->nama_pelanggan ?? '—' }}</dd></div>
                    <div class="border-b border-slate-200 pb-3"><dt class="mb-1 text-body-secondary">Nomor Pemesanan</dt><dd class="mb-0 fw-semibold">{{ $jaminan->id_pemesanan }}</dd></div>
                    <div class="border-b border-slate-200 pb-3"><dt class="mb-1 text-body-secondary">Jenis Dokumen</dt><dd class="mb-0 fw-semibold">{{ $jaminan->jenis_dokumen === 'Surat Tanah' ? 'Sertifikat Tanah' : $jaminan->jenis_dokumen }}</dd></div>
                    <div class="border-b border-slate-200 pb-3"><dt class="mb-1 text-body-secondary">Nomor Dokumen</dt><dd class="mb-0 fw-semibold">{{ $jaminan->nomor_dokumen }}</dd></div>
                    <div class="border-b border-slate-200 pb-3"><dt class="mb-1 text-body-secondary">Nama Pemilik</dt><dd class="mb-0 fw-semibold">{{ $jaminan->nama_pemilik }}</dd></div>
                    <div class="border-b border-slate-200 pb-3"><dt class="mb-1 text-body-secondary">Tanggal Penyerahan</dt><dd class="mb-0 fw-semibold">{{ $jaminan->tanggal_penyerahan?->format('d/m/Y') ?? '—' }}</dd></div>
                    <div>
                        <dt class="text-body-secondary">Status Verifikasi</dt>
                        <dd class="mb-0"><span class="badge bg-label-{{ $statusVerifikasiClass }}">{{ $jaminan->status_verifikasi_label }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-body-secondary">Status Pengembalian</dt>
                        <dd class="mb-0"><span class="badge bg-label-{{ $statusPengembalianClass }}">{{ $jaminan->status_pengembalian_label }}</span></dd>
                    </div>
                    @if ($jaminan->verifier)
                        <div class="sm:col-span-2 lg:col-span-3 border-b border-slate-200 pb-3">
                            <dt class="text-body-secondary">Keputusan Verifikasi</dt>
                            <dd class="mb-0 fw-semibold">{{ $jaminan->verifier->nama_lengkap ?? $jaminan->verifier->username }} · {{ $jaminan->updated_at?->locale('id')->translatedFormat('d M Y, H:i') }}</dd>
                        </div>
                    @endif
                    @if ($jaminan->alasan_penolakan)
                        <div class="sm:col-span-2 lg:col-span-3 rounded border border-danger-subtle bg-danger-subtle p-3">
                            <dt class="fw-semibold text-danger">Alasan Penolakan</dt>
                            <dd class="mb-0 mt-1 whitespace-pre-line">{{ $jaminan->alasan_penolakan }}</dd>
                        </div>
                    @endif
                    <div class="sm:col-span-2 lg:col-span-3 border-b border-slate-200 pb-3">
                        <dt class="mb-1 text-body-secondary">Keterangan Tambahan</dt>
                        <dd class="mb-0 whitespace-pre-line fw-semibold">{{ $jaminan->catatan ?: '—' }}</dd>
                    </div>
                </dl>
                <button type="button" class="btn btn-outline-primary mt-4" id="open-jaminan-preview" data-document-src="{{ route('jaminan.document', $jaminan) }}" data-document-type="{{ $dokumenGambar ? 'image' : 'file' }}">
                    <i class="icon-base bx bx-file me-1" aria-hidden="true"></i>Buka Dokumen Jaminan
                </button>
            </div>

            @if (auth()->user()->peran === 'Pimpinan')
                @if ($jaminan->status_pengembalian !== 'Sudah Dikembalikan')
                    <div class="card-footer border-t">
                        <h2 class="h6 mb-3">Verifikasi oleh Pimpinan</h2>
                        <form method="POST" action="{{ route('jaminan.verify', $jaminan) }}" class="grid grid-cols-1 gap-3 sm:grid-cols-[14rem_1fr_auto]">
                            @csrf
                            @method('PUT')
                            <label class="sr-only" for="status_verifikasi">Keputusan verifikasi</label>
                            <select id="status_verifikasi" class="form-select" name="status_verifikasi" required>
                                <option value="Disetujui" @selected(old('status_verifikasi') === 'Disetujui')>Setujui dokumen</option>
                                <option value="Ditolak" @selected(old('status_verifikasi') === 'Ditolak')>Tolak dokumen</option>
                            </select>
                            <label class="sr-only" for="alasan_penolakan">Alasan penolakan</label>
                            <input id="alasan_penolakan" class="form-control" name="alasan_penolakan" maxlength="2000" placeholder="Wajib diisi jika dokumen ditolak" value="{{ old('alasan_penolakan') }}">
                            <button class="btn btn-success" type="submit">Simpan Keputusan</button>
                        </form>
                    </div>
                @endif
            @else
                <div class="card-footer border-t space-y-4">
                    @if ($jaminan->status_pengembalian !== 'Sudah Dikembalikan')
                        <div>
                            <h2 class="h6 mb-1">Perbaikan Data Jaminan</h2>
                            <p class="mb-3 text-sm text-body-secondary">Perubahan akan mengirim dokumen kembali ke antrean verifikasi Pimpinan.</p>
                            <form method="POST" enctype="multipart/form-data" action="{{ route('jaminan.update', $jaminan) }}" class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="form-label" for="jenis_dokumen">Jenis dokumen</label>
                                    <select id="jenis_dokumen" class="form-select" name="jenis_dokumen" required>
                                        <option value="BPKB" @selected(old('jenis_dokumen', $jaminan->jenis_dokumen) === 'BPKB')>BPKB</option>
                                        <option value="Surat Tanah" @selected(old('jenis_dokumen', $jaminan->jenis_dokumen) === 'Surat Tanah')>Sertifikat Tanah</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" for="nomor_dokumen">Nomor resmi dokumen</label>
                                    <input id="nomor_dokumen" class="form-control" name="nomor_dokumen" required maxlength="100" value="{{ old('nomor_dokumen', $jaminan->nomor_dokumen) }}">
                                </div>
                                <div>
                                    <label class="form-label" for="nama_pemilik">Nama pemilik dokumen</label>
                                    <input id="nama_pemilik" class="form-control" name="nama_pemilik" required maxlength="100" value="{{ old('nama_pemilik', $jaminan->nama_pemilik) }}">
                                </div>
                                <div>
                                    <label class="form-label" for="tanggal_penyerahan">Tanggal penyerahan</label>
                                    <input id="tanggal_penyerahan" class="form-control" type="date" name="tanggal_penyerahan" required value="{{ old('tanggal_penyerahan', $jaminan->tanggal_penyerahan?->toDateString()) }}">
                                </div>
                                <div>
                                    <label class="form-label" for="file_dokumen">Ganti foto / scan dokumen</label>
                                    <input id="file_dokumen" class="form-control" type="file" name="file_dokumen" accept="image/jpeg,image/png,image/webp,application/pdf">
                                    <small class="text-body-secondary">Kosongkan untuk memakai dokumen saat ini. JPG, PNG, WEBP, atau PDF; maksimal 5 MB.</small>
                                </div>
                                <div>
                                    <label class="form-label" for="catatan">Keterangan tambahan</label>
                                    <textarea id="catatan" class="form-control" name="catatan" rows="2" maxlength="2000">{{ old('catatan', $jaminan->catatan) }}</textarea>
                                </div>
                                <button class="btn btn-success md:col-span-2" type="submit">Simpan Perbaikan</button>
                            </form>
                        </div>

                        @if ($bolehDikembalikan)
                            <form method="POST" action="{{ route('jaminan.return', $jaminan) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status_pengembalian" value="Sudah Dikembalikan">
                                <button class="btn btn-outline-success" type="submit">Catat Pengembalian Dokumen</button>
                            </form>
                        @endif
                    @endif
                </div>
            @endif
        </section>

        <div id="jaminan-preview-dialog" class="hidden fixed inset-0 z-[9999] overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="jaminan-preview-title">
            <div id="jaminan-preview-backdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-screen items-center justify-center p-3 sm:p-6">
                <section class="relative z-10 my-auto w-full overflow-hidden rounded-2xl border border-slate-300 bg-slate-100 text-left shadow-2xl" style="width: min(28rem, calc(100vw - 1.5rem)); max-width: 28rem">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 sm:px-6">
                        <h2 id="jaminan-preview-title" class="min-w-0 flex-1 truncate text-sm font-semibold text-slate-800" style="font-size: 14px; line-height: 1.25rem">Preview Dokumen Jaminan</h2>
                        <button type="button" id="close-jaminan-preview" class="shrink-0 rounded-full p-2 text-slate-500 transition hover:bg-slate-200 hover:text-slate-800" aria-label="Tutup preview dokumen" title="Tutup">
                            <i class="icon-base bx bx-x text-2xl" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="max-h-[78vh] overflow-auto p-3 sm:p-4">
                        <div class="flex min-h-[55vh] items-center justify-center rounded-xl bg-slate-200 p-2 sm:p-3">
                            <img id="jaminan-preview-image" alt="Preview gambar dokumen jaminan" src="about:blank" class="hidden max-h-[68vh] max-w-full object-contain">
                            <iframe id="jaminan-preview-frame" title="Preview file dokumen jaminan" src="about:blank" class="hidden w-full rounded border bg-white" style="height: min(68vh, 48rem)"></iframe>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const dialog = document.getElementById('jaminan-preview-dialog');
            const openButton = document.getElementById('open-jaminan-preview');
            const closeButton = document.getElementById('close-jaminan-preview');
            const frame = document.getElementById('jaminan-preview-frame');
            const image = document.getElementById('jaminan-preview-image');
            const backdrop = document.getElementById('jaminan-preview-backdrop');

            if (!dialog || !openButton || !closeButton || !frame || !image || !backdrop) return;

            openButton.addEventListener('click', () => {
                const documentUrl = openButton.dataset.documentSrc;
                if (openButton.dataset.documentType === 'image') {
                    image.src = documentUrl;
                    image.classList.remove('hidden');
                    frame.classList.add('hidden');
                } else {
                    frame.src = documentUrl;
                    frame.classList.remove('hidden');
                    image.classList.add('hidden');
                }
                dialog.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            });

            const closePreview = () => {
                dialog.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                frame.src = 'about:blank';
                image.src = 'about:blank';
                frame.classList.add('hidden');
                image.classList.add('hidden');
                openButton.focus();
            };

            closeButton.addEventListener('click', closePreview);
            backdrop.addEventListener('click', closePreview);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !dialog.classList.contains('hidden')) closePreview();
            });
        })();
    </script>
@endsection
