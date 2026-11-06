@extends('layouts.public')

@section('title', 'Berita & Kegiatan')

@section('content')
    <section class="page-hero">
        <div class="container">
            <h1>Berita &amp; Kegiatan</h1>
            <p>Informasi seputar unit usaha dan aktivitas BUMKam Tuah Rezeki.</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="news-grid">
                <article class="news-card">
                    <img src="{{ asset('assets/img/bumkam-mart-marketfront.png') }}" alt="Tampak depan BUMKam Mart Tuah Rezeki" loading="lazy">
                    <div class="news-card-content"><div class="news-card-meta"><span>Unit Usaha</span><span>BUMKam Tuah Rezeki</span></div><h2>BUMKam Mart</h2><p>Informasi mengenai unit usaha yang menyediakan kebutuhan sehari-hari bagi masyarakat kampung.</p></div>
                </article>
                <article class="news-card">
                    <img src="{{ asset('assets/img/jasa-keuangan-mikro.png') }}" alt="Transaksi layanan keuangan dengan uang Rupiah" loading="lazy">
                    <div class="news-card-content"><div class="news-card-meta"><span>Layanan</span><span>BUMKam Tuah Rezeki</span></div><h2>Jasa Keuangan Mikro</h2><p>Kenali informasi layanan keuangan mikro untuk mendukung kebutuhan dan kegiatan usaha warga.</p></div>
                </article>
                <article class="news-card">
                    <img src="{{ asset('assets/img/pengadaan-barang.png') }}" alt="Gudang penyimpanan barang dan proses pengadaan" loading="lazy">
                    <div class="news-card-content"><div class="news-card-meta"><span>Unit Usaha</span><span>BUMKam Tuah Rezeki</span></div><h2>Pengadaan Barang</h2><p>Informasi umum mengenai penyediaan barang dan material sesuai kebutuhan kampung.</p></div>
                </article>
                <article class="news-card">
                    <img src="{{ asset('assets/img/sewa-tenda.jpeg') }}" alt="Tenda acara yang disewakan BUMKam" loading="lazy">
                    <div class="news-card-content"><div class="news-card-meta"><span>Layanan</span><span>BUMKam Tuah Rezeki</span></div><h2>Sewa Tenda</h2><p>Informasi layanan penyewaan tenda dan perlengkapan untuk kegiatan masyarakat.</p></div>
                </article>
                <article class="news-card">
                    <img src="{{ asset('assets/img/ketahanan-pangan.jpeg') }}" alt="Warga bersama ternak dalam kegiatan ketahanan pangan" loading="lazy">
                    <div class="news-card-content"><div class="news-card-meta"><span>Program</span><span>BUMKam Tuah Rezeki</span></div><h2>Ketahanan Pangan</h2><p>Informasi tentang upaya pengembangan potensi pangan dan kegiatan ekonomi masyarakat.</p></div>
                </article>
                <article class="news-card">
                    <img src="https://images.unsplash.com/photo-1594063835708-b3bb19c6e668?auto=format&amp;fit=crop&amp;w=900&amp;q=80" alt="Lanskap perdesaan" loading="lazy">
                    <div class="news-card-content"><div class="news-card-meta"><span>Kampung</span><span>BUMKam Tuah Rezeki</span></div><h2>Potensi Kampung</h2><p>Kabar dan informasi potensi Kampung Rantau Bertuah akan diperbarui melalui kanal ini.</p></div>
                </article>
            </div>
        </div>
    </section>
@endsection
