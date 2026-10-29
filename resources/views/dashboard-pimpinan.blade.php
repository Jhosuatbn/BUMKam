{{-- Dashboard Pimpinan --}}
@php
    $skalaTransaksi = max($transaksiBulanIni, $transaksiBulanLalu, 1);
    $lebarBulanIni = max(3, (int) round(($transaksiBulanIni / $skalaTransaksi) * 100));
    $lebarBulanLalu = max(3, (int) round(($transaksiBulanLalu / $skalaTransaksi) * 100));
@endphp

<div class="space-y-6 sm:space-y-8">
    <section class="relative isolate overflow-hidden rounded-3xl bg-[#0b1f3a] text-white shadow-xl">
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-[#0b1f3a] via-blue-950 to-[#123f73]"></div>
        <div class="absolute -right-24 -top-32 -z-10 h-96 w-96 rounded-full border-[45px] border-white/[0.045]" aria-hidden="true"></div>
        <div class="absolute -right-5 -top-16 -z-10 h-64 w-64 rounded-full border border-cyan-200/10" aria-hidden="true"></div>
        <div class="absolute bottom-0 left-0 -z-10 h-1 w-2/3 bg-gradient-to-r from-cyan-300 via-blue-400 to-transparent"></div>

        <div class="flex flex-col justify-between gap-8 px-6 py-7 sm:px-9 sm:py-9 lg:flex-row lg:items-end">
            <div class="max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200/20 bg-cyan-200/10 px-3 py-1.5 text-xs font-semibold tracking-wide text-cyan-100">
                        <span class="h-2 w-2 rounded-full bg-cyan-300 shadow-[0_0_10px_rgba(103,232,249,0.7)]" aria-hidden="true"></span>
                        DASHBOARD PIMPINAN
                    </span>
                    <span class="text-xs font-medium text-blue-200/70">BUMKam Tuah Rezeki</span>
                </div>
                <h2 class="mt-5 text-3xl font-bold tracking-tight sm:text-4xl">Kinerja usaha dalam satu pandangan.</h2>
                <p class="mt-3 max-w-xl text-sm leading-relaxed text-blue-100 sm:text-base">
                    Ringkasan aktivitas pemesanan untuk membantu membaca perkembangan usaha dan menentukan langkah selanjutnya.
                </p>
            </div>
            <div class="flex items-center gap-3 self-start rounded-2xl border border-white/10 bg-white/[0.07] px-4 py-3.5 backdrop-blur lg:self-auto">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-white/10 text-cyan-200" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                </span>
                <span>
                    <span class="block text-[11px] font-semibold uppercase tracking-[0.15em] text-blue-200">Periode laporan</span>
                    <span class="mt-1 block text-base font-bold">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('F Y') }}</span>
                </span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-white/10 bg-slate-950/20 px-6 py-3 text-xs text-blue-100/80 sm:px-9">
            <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-cyan-300" aria-hidden="true"></span>Ringkasan bulanan</span>
            <span class="hidden text-blue-200/30 sm:inline">•</span>
            <span>Data transaksi dibandingkan dengan bulan sebelumnya</span>
        </div>
    </section>

    <section aria-labelledby="statistik-heading">
        <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h3 id="statistik-heading" class="text-lg font-bold text-slate-900">Sorotan Kinerja</h3>
                <p class="text-sm text-slate-500">Angka utama yang menggambarkan aktivitas usaha.</p>
            </div>
            <span class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Pembaruan berdasarkan data sistem</span>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <article class="group relative overflow-hidden rounded-2xl border border-blue-100 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg sm:p-6">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-700 to-cyan-400"></div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-600">Transaksi Bulan Ini</p>
                        <p class="mt-1 text-xs text-slate-400">Periode berjalan</p>
                    </div>
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100 transition group-hover:bg-blue-700 group-hover:text-white" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-4 4"/></svg>
                    </span>
                </div>
                <p class="mt-6 text-4xl font-bold tracking-tight text-slate-950 tabular-nums">{{ number_format($transaksiBulanIni, 0, ',', '.') }}</p>
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                    <span class="text-xs text-slate-500">Jumlah pemesanan</span>
                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700">BULAN INI</span>
                </div>
            </article>

            <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg sm:p-6">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-slate-400 to-slate-200"></div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-600">Transaksi Bulan Lalu</p>
                        <p class="mt-1 text-xs text-slate-400">Periode pembanding</p>
                    </div>
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-slate-100 text-slate-600 ring-1 ring-slate-200" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </span>
                </div>
                <p class="mt-6 text-4xl font-bold tracking-tight text-slate-950 tabular-nums">{{ number_format($transaksiBulanLalu, 0, ',', '.') }}</p>
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                    <span class="text-xs text-slate-500">Jumlah pemesanan</span>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">BULAN LALU</span>
                </div>
            </article>

            <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg sm:p-6">
                <div class="absolute inset-x-0 top-0 h-1 {{ $transaksiBulanIni > $transaksiBulanLalu ? 'bg-gradient-to-r from-emerald-500 to-teal-300' : ($transaksiBulanIni < $transaksiBulanLalu ? 'bg-gradient-to-r from-rose-500 to-orange-300' : 'bg-gradient-to-r from-slate-400 to-slate-200') }}"></div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-600">Perubahan Transaksi</p>
                        <p class="mt-1 text-xs text-slate-400">Dibandingkan bulan lalu</p>
                    </div>
                    <span class="grid h-11 w-11 place-items-center rounded-xl
                        {{ $transaksiBulanIni > $transaksiBulanLalu ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' : ($transaksiBulanIni < $transaksiBulanLalu ? 'bg-rose-50 text-rose-700 ring-1 ring-rose-100' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-200') }}" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 17 9 11l4 4 8-8"/><path d="M15 7h6v6"/></svg>
                    </span>
                </div>
                <p class="mt-6 text-4xl font-bold tracking-tight tabular-nums
                    {{ $transaksiBulanIni > $transaksiBulanLalu ? 'text-emerald-700' : ($transaksiBulanIni < $transaksiBulanLalu ? 'text-rose-700' : 'text-slate-950') }}">
                    @if ($transaksiBulanLalu > 0)
                        {{ $persentasePerubahan > 0 ? '+' : '' }}{{ number_format($persentasePerubahan, 1, ',', '.') }}%
                    @elseif ($transaksiBulanIni > 0)
                        —
                    @else
                        0%
                    @endif
                </p>
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                    <span class="text-xs font-medium
                        {{ $transaksiBulanLalu == 0 && $transaksiBulanIni > 0 ? 'text-amber-700' : ($transaksiBulanIni > $transaksiBulanLalu ? 'text-emerald-700' : ($transaksiBulanIni < $transaksiBulanLalu ? 'text-rose-700' : 'text-slate-500')) }}">
                        @if ($transaksiBulanLalu == 0 && $transaksiBulanIni > 0)
                            Pembanding belum tersedia
                        @elseif ($transaksiBulanIni > $transaksiBulanLalu)
                            Mengalami peningkatan
                        @elseif ($transaksiBulanIni < $transaksiBulanLalu)
                            Mengalami penurunan
                        @else
                            Tidak ada perubahan
                        @endif
                    </span>
                    <span class="text-[11px] font-bold text-slate-400">BULANAN</span>
                </div>
            </article>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1.45fr)_minmax(17rem,0.75fr)]">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="perbandingan-heading">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-blue-700">Analisis perbandingan</p>
                    <h3 id="perbandingan-heading" class="mt-1 text-lg font-bold text-slate-900">Pergerakan Transaksi</h3>
                    <p class="mt-1 text-sm text-slate-500">Visualisasi jumlah pemesanan antarperiode.</p>
                </div>
                <span class="inline-flex items-center gap-2 text-xs font-medium text-slate-500"><span class="h-2.5 w-2.5 rounded-full bg-blue-600" aria-hidden="true"></span>Jumlah transaksi</span>
            </div>

            <div class="mt-8 grid grid-cols-[3.5rem_minmax(0,1fr)] items-end gap-4 sm:grid-cols-[5rem_minmax(0,1fr)]">
                <div class="flex h-44 flex-col justify-between pb-8 text-right text-[11px] font-medium text-slate-400" aria-hidden="true">
                    <span>{{ number_format($skalaTransaksi, 0, ',', '.') }}</span>
                    <span>{{ number_format((int) round($skalaTransaksi / 2), 0, ',', '.') }}</span>
                    <span>0</span>
                </div>
                <div class="relative h-44 border-b border-l border-slate-200">
                    <div class="absolute inset-x-0 top-0 border-t border-dashed border-slate-200"></div>
                    <div class="absolute inset-x-0 top-1/2 border-t border-dashed border-slate-200"></div>
                    <div class="absolute inset-x-0 bottom-0 border-t border-slate-200"></div>
                    <div class="absolute inset-0 flex items-end justify-center gap-10 px-5 sm:gap-20">
                        <div class="flex h-full w-20 flex-col items-center justify-end sm:w-28">
                            <span class="mb-2 text-sm font-bold tabular-nums text-slate-800">{{ number_format($transaksiBulanLalu, 0, ',', '.') }}</span>
                            <div class="w-full rounded-t-xl bg-slate-300 transition-all duration-500" style="height: {{ max(5, (int) round(($transaksiBulanLalu / $skalaTransaksi) * 76)) }}%"></div>
                            <span class="absolute -bottom-7 text-xs font-semibold text-slate-500">Bulan lalu</span>
                        </div>
                        <div class="flex h-full w-20 flex-col items-center justify-end sm:w-28">
                            <span class="mb-2 text-sm font-bold tabular-nums text-blue-800">{{ number_format($transaksiBulanIni, 0, ',', '.') }}</span>
                            <div class="w-full rounded-t-xl bg-gradient-to-t from-blue-800 to-cyan-500 shadow-lg shadow-blue-900/10 transition-all duration-500" style="height: {{ max(5, (int) round(($transaksiBulanIni / $skalaTransaksi) * 76)) }}%"></div>
                            <span class="absolute -bottom-7 text-xs font-semibold text-blue-800">Bulan ini</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-12 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                <p class="text-xs text-slate-500">Skala grafik mengikuti nilai transaksi tertinggi pada kedua periode.</p>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-800">Perbandingan bulanan</span>
            </div>
        </article>

        <article class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0b1f3a] via-blue-950 to-blue-800 p-6 text-white shadow-lg" aria-labelledby="insight-heading">
            <div class="absolute -right-10 -top-10 h-36 w-36 rounded-full border-[20px] border-white/[0.06]" aria-hidden="true"></div>
            <div class="relative">
                <span class="grid h-11 w-11 place-items-center rounded-xl border border-white/10 bg-white/10 text-cyan-200" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v2m0 14v2M4.2 4.2l1.4 1.4m12.8 12.8 1.4 1.4M3 12h2m14 0h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/><circle cx="12" cy="12" r="5"/></svg>
                </span>
                <p class="mt-6 text-xs font-bold uppercase tracking-[0.16em] text-cyan-200">Catatan pimpinan</p>
                <h3 id="insight-heading" class="mt-2 text-xl font-bold">Gambaran periode ini</h3>
                <p class="mt-3 text-sm leading-relaxed text-blue-100">
                    @if ($transaksiBulanLalu == 0 && $transaksiBulanIni > 0)
                        Tercatat {{ number_format($transaksiBulanIni, 0, ',', '.') }} transaksi. Data bulan sebelumnya belum tersedia sebagai pembanding.
                    @elseif ($transaksiBulanIni > $transaksiBulanLalu)
                        Aktivitas bertambah {{ number_format($transaksiBulanIni - $transaksiBulanLalu, 0, ',', '.') }} transaksi dari bulan sebelumnya. Pertumbuhan ini menunjukkan perkembangan yang baik.
                    @elseif ($transaksiBulanIni < $transaksiBulanLalu)
                        Aktivitas berkurang {{ number_format($transaksiBulanLalu - $transaksiBulanIni, 0, ',', '.') }} transaksi dari bulan sebelumnya. Perubahan ini dapat menjadi bahan evaluasi.
                    @else
                        Aktivitas transaksi tetap sama dengan bulan sebelumnya. Konsistensi menjadi pijakan untuk terus meningkatkan pelayanan.
                    @endif
                </p>
                <div class="mt-7 border-t border-white/10 pt-5">
                    <p class="text-xs font-medium leading-relaxed text-blue-200">Mengelola potensi dengan amanah, menghadirkan manfaat bagi masyarakat.</p>
                </div>
            </div>
        </article>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="transaksi-terbaru-heading">
        <div class="flex flex-col justify-between gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:px-6">
            <div class="flex items-start gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M8 14h3M8 17h7"/></svg>
                </span>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-blue-700">Aktivitas operasional</p>
                    <h3 id="transaksi-terbaru-heading" class="mt-1 text-lg font-bold text-slate-900">Transaksi Terbaru</h3>
                    <p class="text-sm text-slate-500">Lima pemesanan terakhir yang tercatat dalam sistem.</p>
                </div>
            </div>
            <a href="{{ route('laporan.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
                Lihat Laporan
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>

        @if ($transaksiTerbaru->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[46rem] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3.5 font-semibold sm:px-6">No.</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold sm:px-6">Tanggal Pemesanan</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold sm:px-6">Nama Pelanggan</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold sm:px-6">Total Harga</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold sm:px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($transaksiTerbaru as $transaksi)
                            @php($status = strtolower($transaksi->status_pemesanan ?? ''))
                            <tr class="transition-colors hover:bg-blue-50/50">
                                <td class="whitespace-nowrap px-5 py-4 sm:px-6"><span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">{{ $loop->iteration }}</span></td>
                                <td class="whitespace-nowrap px-5 py-4 font-medium text-slate-700 sm:px-6">{{ \Carbon\Carbon::parse($transaksi->tanggal_pemesanan)->locale('id')->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-4 sm:px-6"><span class="font-semibold text-slate-900">{{ $transaksi->pelanggan?->nama_pelanggan ?? '-' }}</span></td>
                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-900 sm:px-6">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                    @if (str_contains($status, 'batal'))
                                        <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700"><span class="h-1.5 w-1.5 rounded-full bg-rose-500" aria-hidden="true"></span>{{ $transaksi->status_pemesanan }}</span>
                                    @elseif (str_contains($status, 'selesai') || str_contains($status, 'lunas'))
                                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>{{ $transaksi->status_pemesanan }}</span>
                                    @elseif (str_contains($status, 'proses'))
                                        <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700"><span class="h-1.5 w-1.5 rounded-full bg-blue-500" aria-hidden="true"></span>{{ $transaksi->status_pemesanan }}</span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700"><span class="h-1.5 w-1.5 rounded-full bg-amber-500" aria-hidden="true"></span>{{ $transaksi->status_pemesanan ?? 'Belum diketahui' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/70 px-5 py-3 sm:px-6">
                <p class="text-xs text-slate-500">Menampilkan {{ $transaksiTerbaru->count() }} transaksi terbaru.</p>
                <span class="hidden text-xs font-medium text-slate-400 sm:inline">BUMKam Tuah Rezeki</span>
            </div>
        @else
            <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
                <span class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400" aria-hidden="true">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>
                </span>
                <h4 class="mt-4 font-bold text-slate-800">Belum Ada Transaksi</h4>
                <p class="mt-1 text-sm text-slate-500">Data pemesanan akan tampil di sini setelah transaksi tercatat.</p>
            </div>
        @endif
    </section>
</div>
