<!DOCTYPE html>
<html>
<head>
    <title>Tambah Pemesanan</title>
</head>
<body>

    <h1>Tambah Pemesanan</h1>

    @if ($errors->any())
        <div>
            <strong>Terjadi kesalahan:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pemesanan.store') }}" method="POST">
        @csrf

        <div>
            <label>ID Pemesanan</label><br>
            <input
                type="text"
                name="id_pemesanan"
                value="{{ old('id_pemesanan') }}"
                placeholder="Contoh: PM001"
                required
            >
        </div>

        <br>

        <div>
            <label>Pelanggan</label><br>
            <select name="id_pelanggan" required>
                <option value="">-- Pilih Pelanggan --</option>

                @foreach ($pelanggan as $item)
                    <option
                        value="{{ $item->id_pelanggan }}"
                        {{ old('id_pelanggan') == $item->id_pelanggan ? 'selected' : '' }}
                    >
                        {{ $item->nama_pelanggan }}
                    </option>
                @endforeach
            </select>
        </div>

        <br>

        <div>
            <label>Tanggal Pemesanan</label><br>
            <input
                type="date"
                name="tanggal_pemesanan"
                value="{{ old('tanggal_pemesanan', date('Y-m-d')) }}"
                required
            >
        </div>

        <br>

        <hr>

        <h3>Barang</h3>

        <div>
            <label>Barang</label><br>
            <select name="id_barang" required>
                <option value="">-- Pilih Barang --</option>

                @foreach ($barang as $item)
                    <option
                        value="{{ $item->id_barang }}"
                        {{ old('id_barang') == $item->id_barang ? 'selected' : '' }}
                    >
                        {{ $item->nama_barang }}
                        - Rp {{ number_format($item->harga, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
        </div>

        <br>

        <div>
            <label>Jumlah</label><br>
            <input
                type="number"
                name="jumlah"
                value="{{ old('jumlah', 1) }}"
                min="1"
                required
            >
        </div>

        <br>

        <hr>

        <h3>Pembayaran</h3>

        <div>
            <label>Skema Pembayaran</label><br>

            <select name="skema_pembayaran" required>
                <option value="">-- Pilih Skema --</option>

                <option value="DP"
                    {{ old('skema_pembayaran') == 'DP' ? 'selected' : '' }}>
                    DP
                </option>

                <option value="Sebagian"
                    {{ old('skema_pembayaran') == 'Sebagian' ? 'selected' : '' }}>
                    Sebagian
                </option>

                <option value="Tanpa DP"
                    {{ old('skema_pembayaran') == 'Tanpa DP' ? 'selected' : '' }}>
                    Tanpa DP
                </option>
            </select>
        </div>

        <br>

        <div>
            <label>Jumlah DP</label><br>
            <input
                type="number"
                name="dp"
                value="{{ old('dp', 0) }}"
                min="0"
            >
        </div>

        <br>

        <div>
            <label>Tenor (bulan)</label><br>
            <input
                type="number"
                name="tenor"
                value="{{ old('tenor') }}"
                min="1"
                required
            >
        </div>

        <br>

        <div>
            <label>Jaminan</label><br>
            <textarea
                name="jaminan"
                placeholder="Masukkan jaminan jika ada"
            >{{ old('jaminan') }}</textarea>
        </div>

        <br>

        <div>
            <label>Bunga</label><br>
            <input
                type="number"
                name="bunga"
                value="{{ old('bunga', 0) }}"
                min="0"
                step="0.01"
            >
        </div>

        <br>

        <button type="submit">
            Simpan Pemesanan
        </button>

        <a href="{{ route('pemesanan.index') }}">
            Kembali
        </a>

    </form>

</body>
</html>