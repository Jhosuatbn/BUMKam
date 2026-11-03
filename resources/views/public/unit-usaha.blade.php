@extends('layouts.public')

@section('title', 'Unit Usaha')

@section('content')
    <section class="catalog-hero">
        <div class="container">
            <h1>Unit Usaha Kami</h1>
            <p>Jelajahi berbagai produk dan jasa berkualitas dari BUMKam Tuah Rezeki.</p>
        </div>
    </section>

    <section class="section section-soft catalog-section">
        <div class="container">
            <div class="catalog-filter" aria-label="Filter kategori unit usaha">
                <span>Kategori:</span>
                <div class="catalog-filter-list" role="group" aria-label="Pilih kategori">
                    <button type="button" class="catalog-filter-button active" data-category-filter="all" aria-pressed="true">Semua</button>
                    <button type="button" class="catalog-filter-button" data-category-filter="retail" aria-pressed="false">Retail</button>
                    <button type="button" class="catalog-filter-button" data-category-filter="keuangan" aria-pressed="false">Keuangan</button>
                    <button type="button" class="catalog-filter-button" data-category-filter="pengadaan" aria-pressed="false">Pengadaan</button>
                    <button type="button" class="catalog-filter-button" data-category-filter="event" aria-pressed="false">Event</button>
                    <button type="button" class="catalog-filter-button" data-category-filter="pangan" aria-pressed="false">Pangan</button>
                </div>
            </div>

            <div class="unit-grid catalog-grid" data-unit-grid>
                <article class="unit-card catalog-card" data-unit-card data-category="retail">
                    <img class="unit-cover" src="https://images.unsplash.com/photo-1657288649124-b80bdee3c17e?auto=format&amp;fit=crop&amp;w=900&amp;q=80" alt="Sayuran segar di BUMKam Mart" loading="lazy">
                    <div class="unit-body">
                        <span class="catalog-category">Retail</span><h2>BUMKam Mart</h2>
                        <p>Toko kebutuhan sehari-hari lengkap melayani warga kampung dengan harga terjangkau.</p>
                        <span class="catalog-feature">Kebutuhan warga lebih dekat</span>
                        <a class="btn btn-outline" href="mailto:bumkampungtuahrezeki@gmail.com?subject=Pertanyaan%20tentang%20BUMKam%20Mart">Hubungi via Email <span aria-hidden="true">→</span></a>
                    </div>
                </article>
                <article class="unit-card catalog-card" data-unit-card data-category="keuangan">
                    <img class="unit-cover" src="https://images.unsplash.com/photo-1769433455452-fcdbe1a6d107?auto=format&amp;fit=crop&amp;w=900&amp;q=80" alt="Suasana desa" loading="lazy">
                    <div class="unit-body">
                        <span class="catalog-category">Keuangan</span><h2>Unit Simpan Pinjam</h2>
                        <p>Layanan simpan pinjam untuk mendukung usaha warga dengan dana perguliran.</p>
                        <span class="catalog-feature">Informasi layanan melalui pengelola</span>
                        <a class="btn btn-outline" href="mailto:bumkampungtuahrezeki@gmail.com?subject=Pertanyaan%20tentang%20Unit%20Simpan%20Pinjam">Hubungi via Email <span aria-hidden="true">→</span></a>
                    </div>
                </article>
                <article class="unit-card catalog-card" data-unit-card data-category="keuangan">
                    <img class="unit-cover" src="https://images.unsplash.com/photo-1594063835708-b3bb19c6e668?auto=format&amp;fit=crop&amp;w=900&amp;q=80" alt="Lanskap Kampung Rantau Bertuah" loading="lazy">
                    <div class="unit-body">
                        <span class="catalog-category">Keuangan</span><h2>Jasa Keuangan Mikro</h2>
                        <p>Berbagai produk simpanan dan pinjaman untuk UMKM dan warga kampung.</p>
                        <span class="catalog-feature">Konsultasi layanan</span>
                        <a class="btn btn-outline" href="mailto:bumkampungtuahrezeki@gmail.com?subject=Pertanyaan%20tentang%20Jasa%20Keuangan%20Mikro">Hubungi via Email <span aria-hidden="true">→</span></a>
                    </div>
                </article>
                <article class="unit-card catalog-card" data-unit-card data-category="pengadaan">
                    <img class="unit-cover" src="https://images.unsplash.com/photo-1761410201022-cae20bd6abcf?auto=format&amp;fit=crop&amp;w=900&amp;q=80" alt="Kerajinan lokal" loading="lazy">
                    <div class="unit-body">
                        <span class="catalog-category">Pengadaan</span><h2>Pengadaan Barang &amp; Material</h2>
                        <p>Penyediaan barang dan material kebutuhan pembangunan dan operasional kampung.</p>
                        <span class="catalog-feature">Disesuaikan dengan kebutuhan</span>
                        <a class="btn btn-outline" href="mailto:bumkampungtuahrezeki@gmail.com?subject=Pertanyaan%20tentang%20Pengadaan%20Barang">Hubungi via Email <span aria-hidden="true">→</span></a>
                    </div>
                </article>
                <article class="unit-card catalog-card" data-unit-card data-category="event">
                    <img class="unit-cover" src="https://images.unsplash.com/photo-1773802551633-6068010cdaa4?auto=format&amp;fit=crop&amp;w=900&amp;q=80" alt="Perlengkapan kegiatan warga" loading="lazy">
                    <div class="unit-body">
                        <span class="catalog-category">Event</span><h2>Sewa Tenda &amp; Perlengkapan Acara</h2>
                        <p>Penyewaan tenda dan perlengkapan acara untuk kegiatan warga dan kampung.</p>
                        <span class="catalog-feature">Hubungi kami untuk informasi harga</span>
                        <a class="btn btn-outline" href="mailto:bumkampungtuahrezeki@gmail.com?subject=Pertanyaan%20tentang%20Sewa%20Tenda">Hubungi via Email <span aria-hidden="true">→</span></a>
                    </div>
                </article>
                <article class="unit-card catalog-card" data-unit-card data-category="pangan">
                    <img class="unit-cover" src="https://images.unsplash.com/photo-1722252799188-87e1db708544?auto=format&amp;fit=crop&amp;w=900&amp;q=80" alt="Aktivitas masyarakat kampung" loading="lazy">
                    <div class="unit-body">
                        <span class="catalog-category">Pangan</span><h2>Ketahanan Pangan – Budidaya Sapi</h2>
                        <p>Program budidaya sapi untuk mendukung ketahanan pangan dan kesejahteraan warga.</p>
                        <span class="catalog-feature">Peluang kemitraan</span>
                        <a class="btn btn-outline" href="mailto:bumkampungtuahrezeki@gmail.com?subject=Pertanyaan%20tentang%20Ketahanan%20Pangan">Hubungi via Email <span aria-hidden="true">→</span></a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="section section-soft">
        <div class="container">
            <div class="section-heading center"><h2>Testimoni Pelanggan</h2><p>Contoh konten testimoni dari source desain Figma. Ganti dengan testimoni pelanggan yang benar dan telah mendapat izin sebelum dipublikasikan.</p></div>
            <div class="testimonial-grid">
                <article class="testimonial-card"><div class="testimonial-person"><span>BS</span><div><h3>Bapak Sutrisno</h3><p>Pelanggan</p></div></div><p class="testimonial-quote">“Sayuran dari BUMKam Tuah Rezeki sangat segar dan berkualitas. Harganya juga terjangkau. Saya rutin pesan setiap minggu.”</p></article>
                <article class="testimonial-card"><div class="testimonial-person"><span>AW</span><div><h3>Ibu Ani Wijaya</h3><p>Warga</p></div></div><p class="testimonial-quote">“Suasana kampung yang asri dan warga yang ramah membuat saya merasa nyaman.”</p></article>
                <article class="testimonial-card"><div class="testimonial-person"><span>DL</span><div><h3>Dewi Lestari</h3><p>Pelanggan</p></div></div><p class="testimonial-quote">“Bangga bisa mendukung produk dan layanan lokal yang berkualitas.”</p></article>
            </div>
        </div>
    </section>

    <section class="cta-band"><div class="container cta-inner"><div><h2>Tertarik dengan unit usaha kami?</h2><p>Hubungi BUMKam Tuah Rezeki untuk informasi layanan, produk, dan peluang kerja sama.</p></div><a class="btn" href="mailto:bumkampungtuahrezeki@gmail.com">Hubungi Kami <span aria-hidden="true">→</span></a></div></section>

    <script>
        (() => {
            const filterButtons = [...document.querySelectorAll('[data-category-filter]')];
            const cards = [...document.querySelectorAll('[data-unit-card]')];
            const search = new URLSearchParams(window.location.search).get('q')?.trim().toLocaleLowerCase('id') ?? '';
            let activeCategory = 'all';

            const updateCards = () => {
                cards.forEach((card) => {
                    const matchesCategory = activeCategory === 'all' || card.dataset.category === activeCategory;
                    const matchesSearch = !search || card.textContent.toLocaleLowerCase('id').includes(search);
                    card.hidden = !matchesCategory || !matchesSearch;
                });
            };

            filterButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    activeCategory = button.dataset.categoryFilter;
                    filterButtons.forEach((item) => {
                        const active = item === button;
                        item.classList.toggle('active', active);
                        item.setAttribute('aria-pressed', String(active));
                    });
                    updateCards();
                });
            });

            updateCards();
        })();
    </script>
@endsection
