<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') | BUMKam Tuah Rezeki</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    @php
        $navigationItems = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard'],
            ['label' => 'Pelanggan', 'route' => 'pelanggan.index', 'active' => 'pelanggan.*'],
            ['label' => 'Barang', 'route' => 'barang.index', 'active' => 'barang.*'],
            ['label' => 'Pemesanan', 'route' => 'pemesanan.index', 'active' => 'pemesanan.*'],
            ['label' => 'Pembayaran', 'route' => 'pembayaran.index', 'active' => 'pembayaran.*'],
            ['label' => 'Laporan', 'route' => 'laporan.index', 'active' => 'laporan.*'],
            ['label' => 'Pengguna', 'route' => 'pengguna.index', 'active' => 'pengguna.*'],
        ];
        $authenticatedUser = auth()->user();
        $displayName = $authenticatedUser?->nama_lengkap ?? $authenticatedUser?->username ?? 'Admin';
    @endphp

    <div class="min-h-screen lg:grid lg:grid-cols-[16rem_minmax(0,1fr)]">
        <aside class="hidden min-h-screen flex-col bg-[#0b1f3a] text-white lg:sticky lg:top-0 lg:flex lg:h-screen">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 border-b border-white/10 px-6 py-6 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-4px] focus-visible:outline-blue-300">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-500 text-lg font-bold text-white">B</span>
                <span class="min-w-0">
                    <span class="block text-base font-semibold tracking-wide">BUMKam</span>
                    <span class="block text-xs text-slate-300">Tuah Rezeki</span>
                </span>
            </a>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-6" aria-label="Navigasi utama">
                @foreach ($navigationItems as $navigationItem)
                    @php($isActive = request()->routeIs($navigationItem['active']))
                    <a href="{{ route($navigationItem['route']) }}"
                       @class([
                           'group flex min-h-11 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300',
                           'bg-blue-600 text-white shadow-sm' => $isActive,
                           'text-slate-200 hover:bg-white/10 hover:text-white' => ! $isActive,
                       ])
                       @if ($isActive) aria-current="page" @endif>
                        <span @class([
                            'size-2 shrink-0 rounded-full',
                            'bg-white' => $isActive,
                            'bg-slate-500 group-hover:bg-blue-300' => ! $isActive,
                        ]) aria-hidden="true"></span>
                        <span>{{ $navigationItem['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 px-6 py-4 text-xs leading-5 text-slate-300">
                Sistem Pengelolaan BUMKam
            </div>
        </aside>

        <div class="flex min-h-screen min-w-0 flex-col">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
                <div class="flex min-h-[4.5rem] items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 lg:hidden">
                            <span class="grid size-9 place-items-center rounded-lg bg-[#0b1f3a] text-sm font-bold text-white">B</span>
                            <span class="leading-tight">
                                <span class="block text-sm font-semibold text-slate-900">BUMKam</span>
                                <span class="block text-[11px] text-slate-500">Tuah Rezeki</span>
                            </span>
                        </a>

                        <details class="group relative lg:hidden">
                            <summary class="flex size-10 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200 text-slate-700 transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 [&::-webkit-details-marker]:hidden">
                                <span class="sr-only">Buka menu navigasi</span>
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                                </svg>
                            </summary>
                            <nav class="absolute right-0 top-12 z-40 w-64 max-h-[min(70vh,32rem)] overflow-y-auto rounded-xl border border-slate-200 bg-white p-2 shadow-xl" aria-label="Navigasi mobile">
                                @foreach ($navigationItems as $navigationItem)
                                    @php($isActive = request()->routeIs($navigationItem['active']))
                                    <a href="{{ route($navigationItem['route']) }}"
                                       @class([
                                           'flex min-h-11 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600',
                                           'bg-blue-50 text-blue-800' => $isActive,
                                           'text-slate-700 hover:bg-slate-50' => ! $isActive,
                                       ])
                                       @if ($isActive) aria-current="page" @endif>
                                        <span @class([
                                            'size-2 shrink-0 rounded-full',
                                            'bg-blue-600' => $isActive,
                                            'bg-slate-300' => ! $isActive,
                                        ]) aria-hidden="true"></span>
                                        <span>{{ $navigationItem['label'] }}</span>
                                    </a>
                                @endforeach
                            </nav>
                        </details>

                        <div class="hidden min-w-0 lg:block">
                            <p class="truncate text-sm font-semibold text-slate-900">@yield('title', 'Dashboard')</p>
                            <p class="text-xs text-slate-500">BUMKam Tuah Rezeki</p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-3 sm:gap-4">
                        <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                            <span class="grid size-9 place-items-center rounded-full bg-blue-100 text-sm font-semibold text-blue-800" aria-hidden="true">
                                {{ strtoupper(substr($displayName, 0, 1)) }}
                            </span>
                            <span class="hidden max-w-16 truncate text-xs font-medium text-slate-700 min-[360px]:inline sm:max-w-40 sm:text-sm">{{ $displayName }}</span>
                            <span class="sr-only min-[360px]:hidden">Pengguna masuk: {{ $displayName }}</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" aria-label="Keluar" class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-300 hover:bg-slate-50 hover:text-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m9-9h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5" />
                                </svg>
                                <span class="hidden sm:inline">Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main id="main-content" class="min-w-0 flex-1 bg-white px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                <div class="mx-auto w-full max-w-7xl">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>