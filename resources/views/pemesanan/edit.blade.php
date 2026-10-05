<!DOCTYPE html>
<html>
<head>
    <title>Edit Pemesanan</title>
</head>
<body>

    <h1>Edit Pemesanan</h1>

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

    <form action="{{ route('pemesanan.update', $pemesanan->id_pemesanan) }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label>ID Pemesanan</label><br>
            <input
                type="text"
                value="{{ $pemesanan->id_pemesanan }}"
                disabled
            >
        </div>

        <br>

        <div>
            <label>Pelanggan</label><br>
            <select name="id_pelanggan" required>
                @foreach ($pelanggan as $item)
                    <option
                        value="{{ $item->id_pelanggan }}"
                        {{ $pemesanan->id_pelanggan == $item->id_pelanggan ? 'selected' : '' }}
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
                value="{{ $pemesanan->tanggal_pemesanan }}"
                required
            >
        </div>

        <hr>

        <h3>Barang</h3>

        @php
            $detail = $pemesanan->detailPemesanan->first();
        @endphp

        <div>
            <label>Barang</label><br>
            <select name="id_barang" required>
                @foreach ($barang as $item)
                    <option
                        value="{{ $item->id_barang }}"
                        {{ $detail && $detail->id_barang == $item->id_barang ? 'selected' : '' }}
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
                value="{{ $detail->jumlah ?? 1 }}"
                min="1"
                required
            >
        </div>

        <hr>

        <h3>Pembayaran</h3>

        <div>
            <label>Skema Pembayaran</label><br>

            <select name="skema_pembayaran" required>
                <option value="DP"
                    {{ $pemesanan->skema_pembayaran == 'DP' ? 'selected' : '' }}>
                    DP
                </option>

                <option value="Sebagian"
                    {{ $pemesanan->skema_pembayaran == 'Sebagian' ? 'selected' : '' }}>
                    Sebagian
                </option>

                <option value="Tanpa DP"
                    {{ $pemesanan->skema_pembayaran == 'Tanpa DP' ? 'selected' : '' }}>
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
                value="{{ $pemesanan->dp }}"
                min="0"
            >
        </div>

        <br>

        <div>
            <label>Tenor (bulan)</label><br>
            <input
                type="number"
                name="tenor"
                value="{{ $pemesanan->tenor }}"
                min="1"
                required
            >
        </div>

        <br>

        <div>
            <label>Jaminan</label><br>
            <textarea name="jaminan">{{ $pemesanan->jaminan }}</textarea>
        </div>

        <br>

        <div>
            <label>Bunga</label><br>
            <input
                type="number"
                name="bunga"
                value="{{ $pemesanan->bunga }}"
                min="0"
                step="0.01"
            >
        </div>

        <br>

        <button type="submit">
            Update Pemesanan
        </button>

        <a href="{{ route('pemesanan.index') }}">
            Kembali
        </a>

    </form>

</body>
</html>