@extends('layouts.sneat-dashboard')

@section('title', 'Tambah Pemesanan')

@section('content')
<div class="space-y-6">
    <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Tambah Pemesanan</h1>
            <p class="mt-2 text-sm text-slate-600">Isi data pelanggan, barang, dan skema pembayaran.</p>
        </div>
        <a href="{{ route('pemesanan.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Kembali</a>
    </header>

    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
            <p class="font-semibold">Terjadi kesalahan:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <form action="{{ route('pemesanan.store') }}" method="POST" id="formPemesanan" class="space-y-8">
            @csrf

            <fieldset>
                <legend class="text-base font-semibold text-slate-900">Informasi Pemesanan</legend>
                <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                    <div>
                        <label for="id_pemesanan" class="block text-sm font-semibold text-slate-700">ID Pemesanan</label>
                        <input id="id_pemesanan" name="id_pemesanan" type="text"
                               value="{{ old('id_pemesanan', $idPemesananPreview ?? '') }}"
                               readonly required
                               class="mt-2 block min-h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm font-semibold text-slate-700">
                        <p class="mt-1 text-xs text-slate-500">Nomor ditampilkan sebagai pratinjau dan divalidasi saat disimpan.</p>
                    </div>

                    <div>
                        <label for="tanggal_pemesanan" class="block text-sm font-semibold text-slate-700">Tanggal Pemesanan</label>
                        <input id="tanggal_pemesanan" type="date" name="tanggal_pemesanan"
                               value="{{ old('tanggal_pemesanan', date('Y-m-d')) }}" required
                               class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>

                    <div class="relative md:col-span-2">
                        <label for="cari_pelanggan" class="block text-sm font-semibold text-slate-700">Pelanggan</label>
                        <input id="cari_pelanggan" type="text" placeholder="Cari atau pilih nama/ID pelanggan..."
                               autocomplete="off" aria-autocomplete="list" aria-expanded="false"
                               class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        <div id="daftarPelanggan" class="absolute z-30 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg"></div>
                        <select id="id_pelanggan" name="id_pelanggan" class="hidden" tabindex="-1" aria-hidden="true">
                            <option value="">-- Pilih Pelanggan --</option>
                            @foreach ($pelanggan as $item)
                                <option value="{{ $item->id_pelanggan }}"
                                        data-search="{{ strtolower($item->id_pelanggan . ' ' . $item->nama_pelanggan) }}"
                                        {{ old('id_pelanggan') == $item->id_pelanggan ? 'selected' : '' }}>
                                    {{ $item->nama_pelanggan }} ({{ $item->id_pelanggan }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Ketik untuk mencari, lalu pilih pelanggan dari daftar.</p>
                    </div>
                </div>
            </fieldset>

            <fieldset class="border-t border-slate-200 pt-6">
                <legend class="text-base font-semibold text-slate-900">Barang</legend>
                <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                    <div class="relative md:col-span-2">
                        <label for="cari_barang" class="block text-sm font-semibold text-slate-700">Barang</label>
                        <input id="cari_barang" type="text" placeholder="Cari atau pilih nama/ID barang..."
                               autocomplete="off" aria-autocomplete="list" aria-expanded="false"
                               class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        <div id="daftarBarang" class="absolute z-30 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg"></div>
                        <select id="id_barang" name="id_barang" class="hidden" tabindex="-1" aria-hidden="true">
                            <option value="">-- Pilih Barang --</option>
                            @foreach ($barang as $item)
                                <option value="{{ $item->id_barang }}"
                                        data-harga="{{ $item->harga }}"
                                        data-search="{{ strtolower($item->id_barang . ' ' . $item->nama_barang) }}"
                                        {{ old('id_barang') == $item->id_barang ? 'selected' : '' }}>
                                    {{ $item->nama_barang }} - Rp {{ number_format($item->harga, 0, ',', '.') }} — @if (is_null($item->stok)) stok belum diatur @else stok {{ number_format($item->stok, 0, ',', '.') }} @endif
                                </option>
                            @endforeach
                        </select>
                        <p id="hargaSatuan" class="mt-2 text-sm text-slate-500">Harga satuan: -</p>
                        <p class="mt-1 text-xs text-slate-500">Ketik untuk mencari, lalu pilih barang dari daftar.</p>
                    </div>

                    <div>
                        <label for="jumlah" class="block text-sm font-semibold text-slate-700">Jumlah</label>
                        <input id="jumlah" type="number" name="jumlah" value="{{ old('jumlah', 1) }}" min="1" step="1" required
                               class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>

                    <div>
                        <label for="total_harga_tampil" class="block text-sm font-semibold text-slate-700">Total Harga Barang</label>
                        <input id="total_harga_tampil" type="text" value="Rp 0" readonly
                               class="mt-2 block min-h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm font-semibold text-slate-700">
                    </div>
                </div>
            </fieldset>

            <fieldset class="border-t border-slate-200 pt-6">
                <legend class="text-base font-semibold text-slate-900">Skema Pembayaran</legend>
                <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                    <div>
                        <label for="skema_pembayaran" class="block text-sm font-semibold text-slate-700">Skema Pembayaran</label>
                        <select id="skema_pembayaran" name="skema_pembayaran" required
                                class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                            <option value="">-- Pilih Skema --</option>
                            <option value="Tanpa DP" {{ old('skema_pembayaran') == 'Tanpa DP' ? 'selected' : '' }}>Tanpa DP</option>
                            <option value="DP" {{ old('skema_pembayaran') == 'DP' ? 'selected' : '' }}>DP</option>
                        </select>
                    </div>

                    <div id="bagianDP" class="hidden">
                        <label for="dp" class="block text-sm font-semibold text-slate-700">Nominal DP (Rupiah)</label>
                        <div class="mt-2 flex min-h-11 overflow-hidden rounded-xl border border-slate-300 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20">
                            <span class="inline-flex items-center bg-slate-100 px-3 text-sm font-semibold text-slate-600">Rp</span>
                            <input id="dp" type="text" inputmode="numeric" data-rupiah-input name="dp" value="{{ old('dp', 0) }}"
                                   inputmode="numeric" placeholder="Masukkan nominal DP"
                                   class="min-w-0 flex-1 border-0 bg-white px-3 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-0">
                        </div>
                        <p class="mt-1 text-xs text-slate-500">DP tidak boleh melebihi total harga barang.</p>
                    </div>

                    <div>
                        <label for="tenor" class="block text-sm font-semibold text-slate-700">Tenor (bulan)</label>
                        <select id="tenor" name="tenor" required
                                class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                            <option value="">Pilih tenor</option>
                            @foreach ([12, 24, 36] as $pilihanTenor)
                                <option value="{{ $pilihanTenor }}" @selected((string) old('tenor') === (string) $pilihanTenor)>{{ $pilihanTenor }} bulan</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="jaminan" class="block text-sm font-semibold text-slate-700">Jaminan</label>
                        <textarea id="jaminan" name="jaminan" rows="3" placeholder="Masukkan jaminan jika ada"
                                  class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">{{ old('jaminan') }}</textarea>
                    </div>
                </div>
            </fieldset>

            <section class="rounded-2xl border border-blue-200 bg-blue-50/70 p-5 sm:p-6">
                <div class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="16" height="20" x="4" y="2" rx="2"/>
                            <line x1="8" x2="16" y1="6" y2="6"/>
                            <line x1="16" x2="16" y1="14" y2="14"/>
                            <path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 10h.01"/>
                            <path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-900">Ringkasan Angsuran</h2>
                        <p class="text-xs text-slate-600">Perhitungan diperbarui otomatis saat data berubah.</p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-white bg-white p-4">
                        <p class="text-sm text-slate-500">Total Bunga (15%)</p>
                        <p id="ringkasanBunga" class="mt-1 text-lg font-bold text-slate-800">Rp 0</p>
                    </div>
                    <div class="rounded-xl border border-white bg-white p-4">
                        <p class="text-sm text-slate-500">Total Pembayaran</p>
                        <p id="ringkasanTotal" class="mt-1 text-lg font-bold text-slate-800">Rp 0</p>
                        <p class="mt-1 text-xs text-slate-500">Total harga + bunga, belum dikurangi DP.</p>
                    </div>
                    <div class="rounded-xl border border-blue-200 bg-white p-4 sm:col-span-2">
                        <p class="text-sm font-medium text-slate-600">Angsuran per Bulan</p>
                        <p id="ringkasanAngsuran" aria-live="polite" class="mt-2 text-3xl font-extrabold tracking-tight text-blue-700">Rp 0</p>
                        <p id="keteranganAngsuran" class="mt-2 text-sm text-slate-500">Pilih barang, skema pembayaran, dan isi tenor untuk melihat angsuran.</p>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Simpan Pemesanan</button>
                <a href="{{ route('pemesanan.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formPemesanan');
    const cariPelanggan = document.getElementById('cari_pelanggan');
    const selectPelanggan = document.getElementById('id_pelanggan');
    const daftarPelanggan = document.getElementById('daftarPelanggan');
    const cariBarang = document.getElementById('cari_barang');
    const selectBarang = document.getElementById('id_barang');
    const daftarBarang = document.getElementById('daftarBarang');
    const jumlahInput = document.getElementById('jumlah');
    const skemaInput = document.getElementById('skema_pembayaran');
    const dpInput = document.getElementById('dp');
    const bagianDP = document.getElementById('bagianDP');
    const tenorInput = document.getElementById('tenor');
    const hargaSatuan = document.getElementById('hargaSatuan');
    const totalHargaTampil = document.getElementById('total_harga_tampil');
    const ringkasanBunga = document.getElementById('ringkasanBunga');
    const ringkasanTotal = document.getElementById('ringkasanTotal');
    const ringkasanAngsuran = document.getElementById('ringkasanAngsuran');
    const keteranganAngsuran = document.getElementById('keteranganAngsuran');

    const PERSENTASE_BUNGA = 0.15;
    const formatRupiah = nilai => 'Rp ' + Math.round(nilai).toLocaleString('id-ID');
    const pakaiDP = () => ['DP', 'Sebagian'].includes(skemaInput.value);

    function aktifkanDropdown(input, select, dropdown, setelahPilih = null) {
        function sembunyikan() {
            dropdown.classList.add('hidden');
            input.setAttribute('aria-expanded', 'false');
        }

        function tampilkanDaftar() {
            const kataKunci = input.value.trim().toLocaleLowerCase('id-ID');
            const pilihan = Array.from(select.options).filter(option => {
                if (!option.value) return false;
                const teks = (option.dataset.search || option.textContent).toLocaleLowerCase('id-ID');
                return !kataKunci || teks.includes(kataKunci);
            });

            dropdown.innerHTML = '';

            if (pilihan.length === 0) {
                const kosong = document.createElement('div');
                kosong.className = 'px-4 py-3 text-sm text-slate-500';
                kosong.textContent = 'Data tidak ditemukan.';
                dropdown.appendChild(kosong);
            } else {
                pilihan.forEach(option => {
                    const tombol = document.createElement('button');
                    tombol.type = 'button';
                    tombol.className = 'block w-full px-4 py-3 text-left text-sm text-slate-700 hover:bg-blue-50 focus:bg-blue-50 focus:outline-none';
                    tombol.textContent = option.textContent.trim();

                    tombol.addEventListener('click', function () {
                        select.value = option.value;
                        input.value = option.textContent.trim();
                        sembunyikan();
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        if (setelahPilih) setelahPilih();
                    });

                    dropdown.appendChild(tombol);
                });
            }

            dropdown.classList.remove('hidden');
            input.setAttribute('aria-expanded', 'true');
        }

        input.addEventListener('focus', tampilkanDaftar);
        input.addEventListener('input', function () {
            select.value = '';
            tampilkanDaftar();
            if (setelahPilih) setelahPilih();
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') sembunyikan();

            if (event.key === 'ArrowDown') {
                const tombolPertama = dropdown.querySelector('button');
                if (tombolPertama) {
                    event.preventDefault();
                    tombolPertama.focus();
                }
            }
        });

        document.addEventListener('click', function (event) {
            if (!input.parentElement.contains(event.target)) sembunyikan();
        });

        if (select.value) {
            const pilihanLama = select.options[select.selectedIndex];
            if (pilihanLama) input.value = pilihanLama.textContent.trim();
        }
    }

    function aturTampilanDP() {
        const gunakanDP = pakaiDP();
        bagianDP.classList.toggle('hidden', !gunakanDP);
        dpInput.required = gunakanDP;

        if (!gunakanDP) dpInput.value = '0';

        hitungAngsuran();
    }

    function hitungAngsuran() {
        const opsiBarang = selectBarang.options[selectBarang.selectedIndex];
        const harga = opsiBarang && opsiBarang.value ? Number(opsiBarang.dataset.harga || 0) : 0;
        const jumlah = Number(jumlahInput.value);
        const tenor = Number(tenorInput.value);
        const gunakanDP = pakaiDP();
        const totalHarga = harga * jumlah;
        const bunga = totalHarga * PERSENTASE_BUNGA;
        const dp = gunakanDP ? Number(dpInput.value.replace(/\D/g, '') || 0) : 0;
        const totalPembayaran = totalHarga + bunga;

        hargaSatuan.textContent = harga > 0 ? 'Harga satuan: ' + formatRupiah(harga) : 'Harga satuan: -';
        totalHargaTampil.value = formatRupiah(totalHarga);
        ringkasanBunga.textContent = formatRupiah(bunga);
        ringkasanTotal.textContent = formatRupiah(totalPembayaran);

        if (!harga || !Number.isInteger(jumlah) || jumlah < 1 ||
            !Number.isInteger(tenor) || tenor < 1 || !skemaInput.value) {
            ringkasanAngsuran.textContent = 'Rp 0';
            keteranganAngsuran.textContent = 'Pilih barang, skema pembayaran, dan isi tenor untuk melihat angsuran.';
            return;
        }

        if (gunakanDP && dpInput.value.trim() === '') {
            ringkasanAngsuran.textContent = 'Rp 0';
            keteranganAngsuran.textContent = 'Masukkan nominal DP untuk menghitung angsuran.';
            return;
        }

        if (!Number.isFinite(dp) || dp < 0 || dp > totalHarga) {
            ringkasanAngsuran.textContent = 'Belum valid';
            keteranganAngsuran.textContent = 'Nominal DP harus minimal Rp 0 dan tidak boleh melebihi total harga barang.';
            return;
        }

        const angsuran = (totalHarga - dp + bunga) / tenor;
        ringkasanAngsuran.textContent = formatRupiah(angsuran);
        keteranganAngsuran.textContent = 'Dibayar selama ' + tenor + ' bulan' +
            (gunakanDP ? ', dengan DP ' + formatRupiah(dp) + '.' : ', tanpa uang muka.');
    }

    aktifkanDropdown(cariPelanggan, selectPelanggan, daftarPelanggan);
    aktifkanDropdown(cariBarang, selectBarang, daftarBarang, hitungAngsuran);

    selectBarang.addEventListener('change', hitungAngsuran);
    jumlahInput.addEventListener('input', hitungAngsuran);
    skemaInput.addEventListener('change', aturTampilanDP);
    dpInput.addEventListener('input', hitungAngsuran);
    tenorInput.addEventListener('input', hitungAngsuran);

    form.addEventListener('submit', function (event) {
        if (!selectPelanggan.value) {
            event.preventDefault();
            alert('Silakan cari dan pilih pelanggan dari daftar.');
            cariPelanggan.focus();
            return;
        }

        if (!selectBarang.value) {
            event.preventDefault();
            alert('Silakan cari dan pilih barang dari daftar.');
            cariBarang.focus();
            return;
        }

        const opsiBarang = selectBarang.options[selectBarang.selectedIndex];
        const harga = opsiBarang ? Number(opsiBarang.dataset.harga || 0) : 0;
        const jumlah = Number(jumlahInput.value);
        const totalHarga = harga * jumlah;
        const gunakanDP = pakaiDP();
        const dp = gunakanDP ? Number(dpInput.value.replace(/\D/g, '') || 0) : 0;

        if (!Number.isInteger(jumlah) || jumlah < 1) {
            event.preventDefault();
            alert('Jumlah barang minimal 1.');
            jumlahInput.focus();
            return;
        }

        if (!Number.isInteger(Number(tenorInput.value)) || Number(tenorInput.value) < 1) {
            event.preventDefault();
            alert('Tenor harus berupa bilangan bulat minimal 1 bulan.');
            tenorInput.focus();
            return;
        }

        if (!skemaInput.value) {
            event.preventDefault();
            alert('Silakan pilih skema pembayaran.');
            skemaInput.focus();
            return;
        }

        if (gunakanDP && (dpInput.value.trim() === '' || !Number.isFinite(dp) || dp < 0 || dp > totalHarga)) {
            event.preventDefault();
            alert('Periksa kembali nominal DP. DP wajib diisi dan tidak boleh melebihi total harga barang.');
            dpInput.focus();
        }
    });

    aturTampilanDP();
    hitungAngsuran();
});
</script>
@endsection
