@extends('layouts.sneat-dashboard')

@section('title', 'Monitoring Stok')

@section('content')
    @php
        $barangTerlaris = $laris->filter(fn ($item) => (int) $item->total_terjual > 0);
        $penjualanMaksimum = max(1, (int) $barangTerlaris->max('total_terjual'));
    @endphp

    <div class="monitoring-stok-page space-y-5 sm:space-y-6">
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-900" aria-live="assertive">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="monitoring-stok-title">
            <header class="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">Persediaan &amp; pergerakan</p>
                    <h2 id="monitoring-stok-title" class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Monitoring Stok</h2>
                    <p class="mt-1 text-sm text-slate-500">Pantau ketersediaan barang dan tentukan prioritas pengadaan.</p>
                </div>
                <span class="inline-flex w-fit shrink-0 items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800">
                    <span class="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                    Ringkasan persediaan
                </span>
            </header>

            <div class="grid border-t border-slate-200 md:grid-cols-3" aria-label="Ringkasan kondisi stok">
                <article class="border-b border-slate-200 p-5 sm:p-6 md:border-b-0 md:border-r">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-50 text-rose-600" aria-hidden="true"><i class="icon-base bx bx-error-circle text-xl"></i></span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800">Stok kritis</p>
                            <p class="mt-1 text-xs leading-relaxed text-slate-500">Batas ≤ {{ $critical }} unit</p>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-slate-100 pt-3">
                        <p class="text-3xl font-bold tracking-tight text-slate-900 tabular-nums">{{ number_format($stokKritis->count(), 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-slate-500">Perlu segera diisi ulang</p>
                    </div>
                </article>

                <article class="border-b border-slate-200 p-5 sm:p-6 md:border-b-0 md:border-r">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-600" aria-hidden="true"><i class="icon-base bx bx-help-circle text-xl"></i></span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800">Stok belum diatur</p>
                            <p class="mt-1 text-xs leading-relaxed text-slate-500">Belum ada data jumlah stok</p>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-slate-100 pt-3">
                        <p class="text-3xl font-bold tracking-tight text-slate-900 tabular-nums">{{ number_format($barangBelumDiatur->count(), 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-slate-500">Perlu diperiksa Staff</p>
                    </div>
                </article>

                <article class="p-5 sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700" aria-hidden="true"><i class="icon-base bx bx-pause-circle text-xl"></i></span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800">Tidak bergerak</p>
                            <p class="mt-1 text-xs leading-relaxed text-slate-500">Tanpa penjualan selama 90 hari</p>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-slate-100 pt-3">
                        <p class="text-3xl font-bold tracking-tight text-slate-900 tabular-nums">{{ number_format($tidakBergerak->count(), 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-slate-500">Perlu evaluasi persediaan</p>
                    </div>
                </article>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="stok-kritis-heading">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-rose-50 text-rose-600" aria-hidden="true"><i class="icon-base bx bx-package text-xl"></i></span>
                        <div>
                            <h3 id="stok-kritis-heading" class="font-bold text-slate-900">Stok Kritis</h3>
                            <p class="mt-0.5 text-xs text-slate-500">Barang yang perlu diprioritaskan untuk pengadaan.</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">{{ $stokKritis->count() }} barang</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Barang</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Fisik</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Dipesan</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Tersedia</th>
                                <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Harga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($stokKritis as $item)
                                <tr class="transition-colors hover:bg-rose-50/40">
                                    <td class="whitespace-nowrap px-5 py-3.5 font-semibold text-slate-900 sm:px-6">{{ $item->nama_barang }}</td>
                                    <td class="px-4 py-3.5 tabular-nums">{{ number_format($item->stok, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3.5 tabular-nums">{{ number_format($item->stok_dipesan, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3.5"><span class="inline-flex min-w-16 justify-center rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700">{{ number_format($item->stok_tersedia, 0, ',', '.') }} unit</span></td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-right font-medium text-slate-700 sm:px-6">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Tidak ada barang dengan stok kritis.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($barangBelumDiatur->isNotEmpty())
                    <div class="border-t border-amber-100 bg-amber-50/70 px-5 py-3.5 text-sm text-amber-900 sm:px-6">
                        <span class="font-semibold">Perlu pengaturan stok:</span>
                        {{ $barangBelumDiatur->pluck('nama_barang')->join(', ') }}
                    </div>
                @endif
            </article>

            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="terlaris-heading">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-50 text-emerald-700" aria-hidden="true"><i class="icon-base bx bx-trending-up text-xl"></i></span>
                        <div>
                            <h3 id="terlaris-heading" class="font-bold text-slate-900">Barang Paling Laris</h3>
                            <p class="mt-0.5 text-xs text-slate-500">Diurutkan berdasarkan jumlah unit yang terjual.</p>
                        </div>
                    </div>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($barangTerlaris as $item)
                        @php($persentaseTerjual = min(100, max(6, (int) round(((int) $item->total_terjual / $penjualanMaksimum) * 100))))
                        <div class="flex items-center gap-4 px-5 py-4 sm:px-6">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-emerald-50 text-sm font-bold text-emerald-800">{{ $loop->iteration }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ $item->nama_barang }}</p>
                                    <p class="shrink-0 text-sm font-bold text-slate-900 tabular-nums">{{ number_format($item->total_terjual, 0, ',', '.') }} <span class="text-xs font-medium text-slate-500">unit</span></p>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-100" role="img" aria-label="{{ $persentaseTerjual }} persen dari jumlah penjualan tertinggi">
                                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-600 to-lime-500" style="width: {{ $persentaseTerjual }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm text-slate-500">Belum ada penjualan barang pada periode ini.</div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="tidak-bergerak-heading">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-blue-50 text-blue-700" aria-hidden="true"><i class="icon-base bx bx-time-five text-xl"></i></span>
                    <div>
                        <h3 id="tidak-bergerak-heading" class="font-bold text-slate-900">Barang Tidak Bergerak</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Tidak ada penjualan selama 90 hari terakhir.</p>
                    </div>
                </div>
                <span class="w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-800">{{ $tidakBergerak->count() }} barang perlu ditinjau</span>
            </div>
            @if ($tidakBergerak->isNotEmpty())
                <div class="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-3 sm:p-6">
                    @foreach ($tidakBergerak as $item)
                        <article class="monitoring-stok-item rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                            <h4 class="truncate text-sm font-semibold text-slate-800" title="{{ $item->nama_barang }}">{{ $item->nama_barang }}</h4>
                            <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                                <div><p class="text-slate-500">Fisik</p><p class="mt-1 font-semibold text-slate-800 tabular-nums">{{ number_format($item->stok, 0, ',', '.') }}</p></div>
                                <div><p class="text-slate-500">Dipesan</p><p class="mt-1 font-semibold text-slate-800 tabular-nums">{{ number_format($item->stok_dipesan, 0, ',', '.') }}</p></div>
                                <div><p class="text-slate-500">Tersedia</p><p class="mt-1 font-semibold text-emerald-700 tabular-nums">{{ number_format($item->stok_tersedia, 0, ',', '.') }}</p></div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="px-6 py-8 text-center text-sm text-slate-500">Semua barang memiliki pergerakan dalam periode ini.</p>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="riwayat-stok-heading">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-slate-700" aria-hidden="true"><i class="icon-base bx bx-transfer-alt text-xl"></i></span>
                    <div>
                        <h3 id="riwayat-stok-heading" class="font-bold text-slate-900">Riwayat Pergerakan Stok</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Catatan barang masuk dan keluar yang dapat ditelusuri.</p>
                    </div>
                </div>
                <form class="flex w-full gap-2 sm:w-auto" role="search">
                    <label for="stok-search" class="sr-only">Cari nama barang atau ID referensi</label>
                    <input id="stok-search" class="form-control min-w-0 sm:w-72" name="q" value="{{ request('q') }}" placeholder="Cari barang atau referensi">
                    <button type="submit" class="bumkam-green-action inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-xl px-4 text-sm font-semibold text-white">
                        <i class="icon-base bx bx-search" aria-hidden="true"></i><span>Cari</span>
                    </button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="whitespace-nowrap px-5 py-3 font-semibold sm:px-6">Waktu</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Barang</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Aktivitas</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Perubahan</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Saldo</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Referensi</th>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($movements as $m)
                            <tr class="transition-colors hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-5 py-3.5 text-slate-500 sm:px-6">{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-slate-900">{{ $m->barang?->nama_barang ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5">{{ $m->jenis }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 font-bold tabular-nums {{ $m->jumlah_perubahan > 0 ? 'text-emerald-700' : 'text-rose-600' }}">{{ $m->jumlah_perubahan > 0 ? '+' : '' }}{{ number_format($m->jumlah_perubahan, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 tabular-nums">{{ number_format($m->stok_sebelum, 0, ',', '.') }} <span class="text-slate-400">→</span> {{ number_format($m->stok_sesudah, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-slate-500">{{ $m->referensi_id ?: '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3.5 sm:px-6">{{ $m->pengguna?->nama_lengkap ?? $m->pengguna?->username ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada pergerakan stok yang tercatat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($movements->hasPages())
                <div class="border-t border-slate-200 px-5 py-4 sm:px-6">{{ $movements->links() }}</div>
            @endif
        </section>
    </div>
@endsection
