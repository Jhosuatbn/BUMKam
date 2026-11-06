@extends('layouts.sneat-dashboard')
@section('title', 'Audit Trail')
@section('content')
<section class="card overflow-hidden">
    <div class="card-header"><h2 class="card-title mb-1">Audit Trail</h2><p class="mb-0 text-body-secondary">Riwayat aktivitas akun. Password, NIK, dan isi dokumen tidak dicatat.</p></div>
    @if ($errors->any())
        <div role="alert" class="mx-4 mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
            @foreach ($errors->all() as $error)<p class="mb-1 last:mb-0">{{ $error }}</p>@endforeach
        </div>
    @endif
    <div class="card-body"><form method="GET" action="{{ route('audit-trail.index') }}" class="grid grid-cols-1 items-end gap-3 sm:grid-cols-2 lg:grid-cols-6">
        <div class="min-w-0"><label class="form-label mb-1" for="audit-q">Nama / deskripsi</label><input id="audit-q" class="form-control" type="search" name="q" value="{{ $filter['q'] ?? '' }}" placeholder="Cari aktivitas"></div>
        <div class="min-w-0"><label class="form-label mb-1" for="audit-pengguna">Pengguna</label><select id="audit-pengguna" class="form-select" name="pengguna"><option value="">Semua pengguna</option>@foreach ($pengguna as $user)<option value="{{ $user->id_pengguna }}" @selected(($filter['pengguna'] ?? '') === $user->id_pengguna)>{{ $user->nama_lengkap }}</option>@endforeach</select></div>
        <div class="min-w-0"><label class="form-label mb-1" for="audit-jenis">Aktivitas</label><select id="audit-jenis" class="form-select" name="jenis"><option value="">Semua aktivitas</option>@foreach (['login','logout','create','update','delete','verify','approve','reject','handover','cancel','reverse','penyesuaian_denda','account_status','password_reset'] as $type)<option value="{{ $type }}" @selected(($filter['jenis'] ?? '') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>@endforeach</select></div>
        <div class="min-w-0"><label class="form-label mb-1" for="audit-dari">Dari tanggal</label><input id="audit-dari" class="form-control" type="date" name="dari" value="{{ $filter['dari'] ?? '' }}"></div>
        <div class="min-w-0"><label class="form-label mb-1" for="audit-sampai">Sampai tanggal</label><input id="audit-sampai" class="form-control" type="date" name="sampai" value="{{ $filter['sampai'] ?? '' }}"></div>
        <button class="btn btn-primary w-100" type="submit"><i class="icon-base bx bx-filter-alt me-1" aria-hidden="true"></i>Filter</button>
    </form></div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th><th>Modul</th><th>Deskripsi</th><th>IP</th></tr></thead><tbody>
        @forelse ($aktivitas as $item)<tr><td class="text-nowrap">{{ $item->created_at?->timezone(config('kredit.zona_waktu', 'Asia/Jakarta'))->format('d/m/Y H:i:s T') }}</td><td>{{ $item->nama_pengguna }}<small class="d-block text-body-secondary">{{ $item->peran_pengguna ?? 'Sistem' }}</small></td><td><span class="badge bg-label-primary">{{ ucfirst(str_replace('_', ' ', $item->jenis_aktivitas)) }}</span></td><td>{{ $item->modul }}</td><td>{{ $item->deskripsi }}</td><td class="text-nowrap">{{ $item->ip_address ?? '—' }}</td></tr>
        @empty<tr><td colspan="6" class="py-5 text-center text-body-secondary">Belum ada aktivitas untuk filter ini.</td></tr>@endforelse
    </tbody></table></div>
    @if ($aktivitas->hasPages())
        @php
            $halamanSekarang = $aktivitas->currentPage();
            $halamanTerakhir = $aktivitas->lastPage();
            $halamanAwal = max(1, min($halamanSekarang - 1, $halamanTerakhir - 1));
            $halamanAkhir = min($halamanTerakhir, $halamanAwal + 1);
        @endphp
        <nav class="card-footer" role="navigation" aria-label="Pagination Navigation">
            <div class="flex flex-col items-center justify-between gap-3 sm:flex-row">
                <p class="m-0 text-sm text-gray-700 dark:text-gray-400">
                    Showing <span class="font-medium">{{ $aktivitas->firstItem() ?? 0 }}</span>
                    to <span class="font-medium">{{ $aktivitas->lastItem() ?? 0 }}</span>
                    of <span class="font-medium">{{ $aktivitas->total() }}</span> results
                </p>
                <div class="inline-flex rtl:flex-row-reverse rounded-md shadow-sm">
                @if ($aktivitas->onFirstPage())
                    <span aria-disabled="true" aria-label="Previous page"><span class="inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-not-allowed rounded-l-md leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400" aria-hidden="true"><i class="icon-base bx bx-chevron-left text-xl"></i></span></span>
                @else
                    <a href="{{ $aktivitas->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:active:bg-gray-700 dark:focus:border-blue-800 dark:text-gray-300 dark:hover:bg-gray-900 dark:hover:text-gray-300" aria-label="Previous page"><i class="icon-base bx bx-chevron-left text-xl" aria-hidden="true"></i></a>
                @endif

                @for ($halaman = $halamanAwal; $halaman <= $halamanAkhir; $halaman++)
                    @if ($halaman === $halamanSekarang)
                        <span aria-current="page"><span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 cursor-default leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">{{ $halaman }}</span></span>
                    @else
                        <a href="{{ $aktivitas->url($halaman) }}" class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-700 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:text-gray-300 dark:active:bg-gray-700 dark:focus:border-blue-800 hover:bg-gray-100 dark:hover:bg-gray-900" aria-label="Go to page {{ $halaman }}">{{ $halaman }}</a>
                    @endif
                @endfor

                @if ($aktivitas->hasMorePages())
                    <a href="{{ $aktivitas->nextPageUrl() }}" rel="next" class="inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:active:bg-gray-700 dark:focus:border-blue-800 dark:text-gray-300 dark:hover:bg-gray-900 dark:hover:text-gray-300" aria-label="Next page"><i class="icon-base bx bx-chevron-right text-xl" aria-hidden="true"></i></a>
                @else
                    <span aria-disabled="true" aria-label="Next page"><span class="inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-not-allowed rounded-r-md leading-5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400" aria-hidden="true"><i class="icon-base bx bx-chevron-right text-xl"></i></span></span>
                @endif
                </div>
            </div>
        </nav>
    @endif
</section>
@endsection
