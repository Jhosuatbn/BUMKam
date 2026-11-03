@extends('layouts.public')

@section('title', 'Profil')

@section('content')
    <section class="page-hero"><div class="container"><span class="eyebrow">Mengenal lebih dekat</span><h1>Profil BUMKam Tuah Rezeki</h1><p>Perjalanan usaha kampung yang dibangun melalui semangat kebersamaan, tata kelola yang amanah, dan pengembangan potensi lokal.</p></div></section>
    <section class="section section-soft"><div class="container"><div class="section-heading center"><span class="section-kicker">Arah Kami</span><h2>Visi dan Misi</h2><p>Landasan dalam mengelola usaha dan memberikan pelayanan kepada masyarakat.</p></div><div class="profile-grid"><article class="profile-card"><h3>Visi</h3><p>Menjadi motor penggerak ekonomi dan pusat pelayanan kebutuhan masyarakat kampung.</p></article><article class="profile-card"><h3>Misi</h3><ul><li>Meningkatkan kontribusi usaha bagi pendapatan kampung.</li><li>Mendorong kegiatan ekonomi masyarakat.</li><li>Meningkatkan kesejahteraan warga melalui pelayanan yang baik.</li><li>Mengelola aset kampung secara tertib dan bertanggung jawab.</li></ul></article></div></div></section>
    <section class="section"><div class="container"><div class="section-heading center"><span class="section-kicker">Perjalanan Kami</span><h2>Sejarah BUMKam</h2><p>Perkembangan kelembagaan dari awal berdiri hingga saat ini.</p></div><div class="timeline-list">
        <article><span>2007</span><div><h3>Pendirian UED-SP Tuah Rezeki</h3><p>Program UED-SP menjadi cikal bakal pengembangan usaha kampung.</p></div></article>
        <article><span>2010</span><div><h3>Transformasi menjadi BUMDes</h3><p>Perubahan kelembagaan sesuai Peraturan Kampung No. 01 Tahun 2010.</p></div></article>
        <article><span>2015</span><div><h3>Rebranding menjadi BUMKam</h3><p>Penamaan resmi menjadi BUMKam (Badan Usaha Milik Kampung).</p></div></article>
        <article><span>2022</span><div><h3>Berbadan hukum resmi</h3><p>Mendapat Sertifikat Kemenkumham AHU-04629 sebagai badan hukum resmi.</p></div></article>
        <article><span>{{ date('Y') }}</span><div><h3>Terus bertumbuh</h3><p>Berupaya memberikan pelayanan dan manfaat yang berkelanjutan bagi warga.</p></div></article>
    </div></div></section>
    <section class="section section-soft"><div class="container"><div class="section-heading center"><h2>Struktur Organisasi</h2><p>Tim pengurus yang berdedikasi untuk kemajuan BUMKam.</p></div><div class="org-groups">
        <div class="org-authority">Otoritas: Musyawarah Kampung</div>
        <div class="org-grid org-advisors"><div class="org-item"><span>Penasehat</span><strong>Hendri Wijaya, S.Kom.</strong></div><div class="org-item"><span>Fasilitator</span><strong>M. Muslim Saragi, SP</strong></div><div class="org-item"><span>Pengawas</span><strong>Syafniwati</strong></div></div>
        <div class="org-grid org-director"><div class="org-item"><span>Direktur</span><strong>Anton Hidayat, SH.</strong></div></div>
        <div class="org-grid org-leadership"><div class="org-item"><span>Sekretaris</span><strong>Mimi Yuldiharti, SE.</strong></div><div class="org-item"><span>Bendahara</span><strong>Dewi Wahyuni, AMd.Keb.</strong></div></div>
        <h3 class="org-group-title">Kepala Unit</h3><div class="org-grid org-units"><div class="org-item"><span>Kepala Unit JKM-SP</span><strong>Dewi Wahyuni</strong></div><div class="org-item"><span>Kepala Unit Barang</span><strong>Baizal Anisrah</strong></div><div class="org-item"><span>Kepala Unit Mart</span><strong>Neneng Kurniasih</strong></div><div class="org-item"><span>Kepala Unit Tenda</span><strong>Reza Andrian</strong></div><div class="org-item"><span>Kepala Unit Pangan</span><strong>Kelompok Tani</strong></div></div>
        <h3 class="org-group-title">Staf Analis</h3><div class="org-grid org-analysts"><div class="org-item"><span>Staf Analis</span><strong>Reza Andrian, SE</strong></div><div class="org-item"><span>Staf Analis</span><strong>Baizal Anisrah, S.Pd.</strong></div></div>
    </div></div></section>
@endsection
