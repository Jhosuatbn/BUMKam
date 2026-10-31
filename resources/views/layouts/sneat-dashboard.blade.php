<!doctype html>
<html lang="id" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets') }}/" data-base-url="{{ url('/') }}" data-framework="laravel" data-bs-theme="light" data-template="vertical-menu-template">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | BUMKam Tuah Rezeki</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    @stack('dashboard-data')
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/sneat.js'])
</head>
<body>
    @php
        $authenticatedUser = auth()->user();
        $displayName = $authenticatedUser?->nama_lengkap ?? $authenticatedUser?->username ?? 'Pengguna';
        $navigationItems = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'bx-home-smile'],
            ['label' => 'Pelanggan', 'route' => 'pelanggan.index', 'active' => 'pelanggan.*', 'icon' => 'bx-group'],
            ['label' => 'Barang', 'route' => 'barang.index', 'active' => 'barang.*', 'icon' => 'bx-package'],
        ];
        if ($authenticatedUser?->peran !== 'Pimpinan') {
            $navigationItems = array_merge($navigationItems, [
                ['label' => 'Pemesanan', 'route' => 'pemesanan.index', 'active' => 'pemesanan.*', 'icon' => 'bx-cart-alt'],
                ['label' => 'Pembayaran', 'route' => 'pembayaran.index', 'active' => 'pembayaran.*', 'icon' => 'bx-wallet'],
            ]);
        }
        $navigationItems[] = ['label' => 'Monitoring Kredit', 'route' => 'monitoring-kredit.index', 'active' => 'monitoring-kredit.*', 'icon' => 'bx-line-chart'];
        $navigationItems[] = ['label' => 'Laporan', 'route' => 'laporan.index', 'active' => 'laporan.*', 'icon' => 'bx-file'];
        if ($authenticatedUser?->peran !== 'Pimpinan') {
            $navigationItems[] = ['label' => 'Pengguna', 'route' => 'pengguna.index', 'active' => 'pengguna.*', 'icon' => 'bx-user'];
        }

        $currentPageTitle = match (true) {
            request()->routeIs('pelanggan.index') => 'Data Pelanggan',
            request()->routeIs('barang.index') => 'Data Barang',
            request()->routeIs('pemesanan.index') => 'Data Pemesanan',
            request()->routeIs('pembayaran.index') => 'Data Pembayaran',
            request()->routeIs('pengguna.index') => 'Data Pengguna',
            request()->routeIs('monitoring-kredit.index') => 'Monitoring Kredit',
            request()->routeIs('laporan.*') => 'Laporan',
            default => trim($__env->yieldContent('title')) ?: 'Dashboard',
        };
        $currentPageIcon = match (true) {
            request()->routeIs('dashboard') => 'bx-home-smile',
            request()->routeIs('pelanggan.*') => 'bx-group',
            request()->routeIs('barang.*') => 'bx-package',
            request()->routeIs('pemesanan.*') => 'bx-cart-alt',
            request()->routeIs('pembayaran.*') => 'bx-wallet',
            request()->routeIs('monitoring-kredit.*') => 'bx-line-chart',
            request()->routeIs('laporan.*') => 'bx-file',
            request()->routeIs('pengguna.*') => 'bx-user',
            default => 'bx-grid-alt',
        };

    @endphp
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="{{ route('dashboard') }}" class="app-brand-link">
                        <span class="app-brand-logo demo"><img class="bumkam-sidebar-logo" src="{{ asset('assets/img/bumkam-mark.png') }}" alt="Logo BUMKam Tuah Rezeki"></span>
                        <span class="app-brand-text demo menu-text fw-bold ms-2">BUMKam</span>
                    </a>
                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none" aria-label="Tutup menu"><i class="icon-base bx bx-chevron-left icon-sm"></i></a>
                </div>
                <div class="menu-divider mt-0"></div>
                <div class="menu-inner-shadow"></div>
                <ul class="menu-inner py-1">
                    <li class="menu-header small text-uppercase"><span class="menu-header-text">Menu Utama</span></li>
                    @foreach ($navigationItems as $navigationItem)
                        @php($active = request()->routeIs($navigationItem['active']))
                        @if ($navigationItem['label'] === 'Pelanggan')<li class="menu-header small text-uppercase"><span class="menu-header-text">Data Usaha</span></li>@endif
                        @if ($navigationItem['label'] === 'Monitoring Kredit')<li class="menu-header small text-uppercase"><span class="menu-header-text">Pengawasan</span></li>@endif
                        @if ($navigationItem['label'] === 'Laporan')<li class="menu-header small text-uppercase"><span class="menu-header-text">Laporan</span></li>@endif
                        @if ($navigationItem['label'] === 'Pengguna')<li class="menu-header small text-uppercase"><span class="menu-header-text">Pengelolaan</span></li>@endif
                        <li @class(['menu-item', 'active' => $active])>
                            <a href="{{ route($navigationItem['route']) }}" class="menu-link" @if ($active) aria-current="page" @endif>
                                <i class="menu-icon tf-icons bx {{ $navigationItem['icon'] }}"></i><div class="text-truncate">{{ $navigationItem['label'] }}</div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </aside>

            <div class="layout-page">
                <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none"><a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)" aria-label="Buka menu"><i class="icon-base bx bx-menu icon-md"></i></a></div>
                        <div class="d-flex min-w-0 align-items-center gap-2 me-auto">
                        <span class="avatar flex-shrink-0"><span class="avatar-initial rounded bg-label-primary"><i class="icon-base bx {{ $currentPageIcon }}" aria-hidden="true"></i></span></span>
                        <div class="min-w-0">
                                <span class="d-block text-heading fw-semibold text-nowrap">{{ $currentPageTitle }}</span>
                                <small class="d-block text-nowrap text-body-secondary">BUMKam Tuah Rezeki</small>
                        </div>
                    </div>
                    <div class="navbar-nav-right d-flex flex-grow-0 flex-shrink-0 align-items-center justify-content-end ms-auto" id="navbar-collapse" style="flex-basis: auto">
                        <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown"><div class="avatar avatar-online"><span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(substr($displayName, 0, 1)) }}</span></div></a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><span class="dropdown-item-text"><span class="fw-semibold d-block">{{ $displayName }}</span><small class="text-body-secondary">{{ $authenticatedUser->peran === 'Pimpinan' ? 'Pimpinan' : 'Admin / Staff' }}</small></span></li>
                                    <li><div class="dropdown-divider my-1"></div></li>
                                    <li><form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="dropdown-item"><i class="icon-base bx bx-power-off icon-md me-3"></i><span>Keluar</span></button></form></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">@yield('content')</div>
                    <footer class="content-footer footer bg-footer-theme"><div class="container-xxl d-flex flex-wrap justify-content-between py-3 flex-md-row flex-column"><div class="mb-2 mb-md-0">© {{ date('Y') }} BUMKam Tuah Rezeki</div><div class="text-body-secondary">Template by <a href="https://themeselection.com/" target="_blank" rel="noopener">ThemeSelection</a></div></div></footer>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div><div class="drag-target"></div>
    </div>
    @include('components.delete-confirmation-dialog')
</body>
</html>
