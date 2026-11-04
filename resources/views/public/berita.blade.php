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
                    <img src="https://images.unsplash.com/photo-1657288649124-b80bdee3c17e?auto=format&amp;fit=crop&amp;w=900&amp;q=80" alt="Produk segar untuk warga" loading="lazy">
                    <div class="news-card-content"><div class="news-card-meta"><span>Unit Usaha</span><span>BUMKam Tuah Rezeki</span></div><h2>BUMKam Mart</h2><p>Informasi mengenai unit usaha yang menyediakan kebutuhan sehari-hari bagi masyarakat kampung.</p></div>
                </article>
                <article class="news-card">
                    <img src="https://images.unsplash.com/photo-1769433455452-fcdbe1a6d107?auto=format&amp;fit=crop&amp;w=900&amp;q=80" alt="Pemandangan kampung" loading="lazy">
                    <div class="news-card-content"><div class="news-card-meta"><span>Layanan</span><span>BUMKam Tuah Rezeki</span></div><h2>Jasa Keuangan Mikro</h2><p>Kenali informasi layanan keuangan mikro untuk mendukung kebutuhan dan kegiatan usaha warga.</p></div>
                </article>
                <article class="news-card">
                    <img src="https://images.unsplash.com/photo-1761410201022-cae20bd6abcf?auto=format&amp;fit=crop&amp;w=900&amp;q=80" alt="Kerajinan lokal" loading="lazy">
                    <div class="news-card-content"><div class="news-card-meta"><span>Unit Usaha</span><span>BUMKam Tuah Rezeki</span></div><h2>Pengadaan Barang</h2><p>Informasi umum mengenai penyediaan barang dan material sesuai kebutuhan kampung.</p></div>
                </article>
                <article class="news-card">
                    <img src="https://images.unsplash.com/photo-1773802551633-6068010cdaa4?auto=format&amp;fit=crop&amp;w=900&amp;q=80" alt="Perlengkapan kegiatan" loading="lazy">
                    <div class="news-card-content"><div class="news-card-meta"><span>Layanan</span><span>BUMKam Tuah Rezeki</span></div><h2>Sewa Tenda</h2><p>Informasi layanan penyewaan tenda dan perlengkapan untuk kegiatan masyarakat.</p></div>
                </article>
                <article class="news-card">
                    <img src="https://images.unsplash.com/photo-1722252799188-87e1db708544?auto=format&amp;fit=crop&amp;w=900&amp;q=80" alt="Aktivitas masyarakat" loading="lazy">
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
