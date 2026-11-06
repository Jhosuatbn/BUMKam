@extends('layouts.public')

@section('title', 'Beranda')

@section('content')
    <section class="hero">
        <img class="hero-image" src="https://images.unsplash.com/photo-1594063835708-b3bb19c6e668?auto=format&amp;fit=crop&amp;w=2200&amp;q=85" alt="" fetchpriority="high">
        <div class="container hero-content">
            <h1>Menebar Manfaat,<br><span>Menjemput Tuah Rezeki</span></h1>
            <p class="hero-lead">Penggerak Ekonomi Desa yang Mandiri, Amanah, dan Terpercaya sejak 2007</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="#tentang">Mulai Menjelajah <span aria-hidden="true">↓</span></a>
            </div>
        </div>
    </section>

    <section class="section" id="tentang">
        <div class="container about-grid">
            <div class="about-copy">
                <div class="section-heading">
                    <span class="section-kicker">Tentang Kami</span>
                    <h2>Membangun usaha, menguatkan kampung.</h2>
                    <p>BUMKam Tuah Rezeki berdiri sejak 2007, bertransformasi dari UED-SP menjadi pilar ekonomi Kampung Rantau Bertuah. Kami mengelola kekayaan desa untuk meningkatkan kesejahteraan masyarakat melalui berbagai unit usaha strategis.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section section-soft">
        <div class="container">
            <div class="section-heading center"><span class="section-kicker">Unit Usaha</span><h2>Unit Usaha Kami</h2><p>Berbagai bidang usaha yang dikelola untuk kesejahteraan warga kampung.</p></div>
            <div class="unit-grid">
                <a class="unit-card" href="{{ route('public.unit-usaha') }}"><div class="unit-image-wrap"><img class="unit-cover unit-cover-mart" src="{{ asset('assets/img/bumkam-mart-marketfront.png') }}" alt="Tampak depan BUMKam Mart Tuah Rezeki" loading="lazy"><span class="unit-image-icon" aria-hidden="true">🛒</span></div><div class="unit-body"><h3>BUMKam Mart</h3><p>Toko kebutuhan sehari-hari melayani warga kampung.</p></div></a>
                <a class="unit-card" href="{{ route('public.unit-usaha') }}"><div class="unit-image-wrap"><img class="unit-cover" src="{{ asset('assets/img/jasa-keuangan-mikro.png') }}" alt="Transaksi layanan keuangan dengan uang Rupiah" loading="lazy"><span class="unit-image-icon" aria-hidden="true">💳</span></div><div class="unit-body"><h3>Jasa Keuangan Mikro</h3><p>Simpan pinjam untuk mendukung usaha warga.</p></div></a>
                <a class="unit-card" href="{{ route('public.unit-usaha') }}"><div class="unit-image-wrap"><img class="unit-cover" src="{{ asset('assets/img/pengadaan-barang.png') }}" alt="Gudang penyimpanan barang dan proses pengadaan" loading="lazy"><span class="unit-image-icon" aria-hidden="true">📦</span></div><div class="unit-body"><h3>Pengadaan Barang</h3><p>Penyediaan barang dan material kebutuhan kampung.</p></div></a>
                <a class="unit-card" href="{{ route('public.unit-usaha') }}"><div class="unit-image-wrap"><img class="unit-cover" src="{{ asset('assets/img/sewa-tenda.jpeg') }}" alt="Tenda acara yang disewakan BUMKam" loading="lazy"><span class="unit-image-icon" aria-hidden="true">⛺</span></div><div class="unit-body"><h3>Sewa Tenda</h3><p>Penyewaan tenda untuk acara warga dan pesta.</p></div></a>
                <a class="unit-card" href="{{ route('public.unit-usaha') }}"><div class="unit-image-wrap"><img class="unit-cover" src="{{ asset('assets/img/ketahanan-pangan.jpeg') }}" alt="Warga bersama ternak dalam kegiatan ketahanan pangan" loading="lazy"><span class="unit-image-icon" aria-hidden="true">🐄</span></div><div class="unit-body"><h3>Ketahanan Pangan</h3><p>Budidaya sapi untuk mendukung ketahanan pangan.</p></div></a>
                <a class="unit-card" href="{{ route('public.unit-usaha') }}"><div class="unit-image-wrap"><img class="unit-cover" src="https://images.unsplash.com/photo-1594063835708-b3bb19c6e668?auto=format&amp;fit=crop&amp;w=900&amp;q=80" alt="Lanskap desa" loading="lazy"><span class="unit-image-icon" aria-hidden="true">🏘️</span></div><div class="unit-body"><h3>Unit Strategis Lainnya</h3><p>Pengembangan unit usaha sesuai potensi kampung.</p></div></a>
            </div>
            <div style="text-align:center;margin-top:30px"><a class="btn btn-outline" href="{{ route('public.unit-usaha') }}">Jelajahi Semua Unit <span aria-hidden="true">→</span></a></div>
        </div>
    </section>

    <section class="stats-band" aria-label="Ringkasan BUMKam">
        <div class="container stats-grid">
            <div class="stat"><strong>17+</strong><span>Tahun Pengalaman</span></div>
            <div class="stat"><strong>Rp 9,6M+</strong><span>Dana Perguliran</span></div>
            <div class="stat"><strong>6</strong><span>Unit Usaha</span></div>
            <div class="stat"><strong>2007</strong><span>Tahun Berdiri</span></div>
        </div>
    </section>

    <section class="section" id="kontak">
        <div class="container">
            <div class="section-heading"><span class="section-kicker">Mari Terhubung</span><h2>Kunjungi dan hubungi kami.</h2><p>Kami terbuka untuk pertanyaan, kerja sama, dan peluang yang dapat mendukung kemajuan ekonomi kampung.</p></div>
            <div class="contact-grid">
                <div class="map-placeholder map-frame"><iframe title="Peta Kampung Rantau Bertuah, Siak" src="https://maps.google.com/maps?q=Kampung%20Rantau%20Bertuah%2C%20Kabupaten%20Siak%2C%20Riau&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe><a class="map-link" href="https://maps.google.com/?q=Kampung+Rantau+Bertuah+Siak" target="_blank" rel="noopener">Buka Google Maps <span aria-hidden="true">↗</span></a></div>
                <div class="contact-card">
                    <h3 style="margin:0 0 8px;font:800 18px 'Manrope',sans-serif">Informasi Kontak</h3>
                    <div class="contact-item"><span class="contact-icon" aria-hidden="true">⌖</span><div><small>Alamat</small><strong>Kampung Rantau Bertuah, Kabupaten Siak, Riau</strong></div></div>
                    <div class="contact-item"><span class="contact-icon" aria-hidden="true">✉</span><div><small>Email</small><a href="mailto:bumkampungtuahrezeki@gmail.com">bumkampungtuahrezeki@gmail.com</a><a href="mailto:bumkampungtuahrezeki2@gmail.com">bumkampungtuahrezeki2@gmail.com</a></div></div>
                    <div class="contact-item"><span class="contact-icon" aria-hidden="true">f</span><div><small>Facebook</small><a href="https://facebook.com/bumkamtuahrezeki" target="_blank" rel="noopener">Bumkam Tuah Rezeki</a></div></div>
                    <div class="contact-item"><span class="contact-icon" aria-hidden="true">◷</span><div><small>Jam Operasional</small><strong>Senin–Jumat: 08.00–16.00 WIB<br>Sabtu: 08.00–12.00 WIB</strong></div></div>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container community-cta">
            <span class="community-icon" aria-hidden="true">♧</span>
            <h2>Siap berkolaborasi?</h2>
            <p>Kami terbuka untuk kemitraan dan kerja sama yang mendukung kemajuan ekonomi Kampung Rantau Bertuah.</p>
            <a class="btn btn-primary" href="https://wa.me/6285830211793?text=Halo%20BUMKam%20Tuah%20Rezeki%2C%20saya%20ingin%20bertanya%20tentang%20kemitraan%20dan%20kerja%20sama." target="_blank" rel="noopener noreferrer">Hubungi BUMKam <span aria-hidden="true">→</span></a>
        </div>
    </section>

@endsection
