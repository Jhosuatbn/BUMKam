<!DOCTYPE html>
<html>
<head>
    <title>Tambah Pembayaran</title>
</head>
<body>

    <h1>Tambah Pembayaran</h1>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('pembayaran.store') }}" method="POST">
        @csrf

        <div>
            <label>ID Pembayaran</label><br>
            <input type="text" name="id_pembayaran"
                   value="{{ old('id_pembayaran') }}"
                   placeholder="Contoh: BYR-001">
        </div>

        <br>

        <div>
            <label>Pemesanan</label><br>
            <select name="id_pemesanan">
                <option value="">-- Pilih Pemesanan --</option>

                @foreach($pemesanan as $item)
                    <option value="{{ $item->id_pemesanan }}"
                        {{ old('id_pemesanan') == $item->id_pemesanan ? 'selected' : '' }}>
                        {{ $item->id_pemesanan }}
                        - {{ $item->pelanggan->nama_pelanggan ?? '-' }}
                        - Rp {{ number_format($item->total_harga, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
        </div>

        <br>

        <div>
            <label>Tanggal Pembayaran</label><br>
            <input type="date" name="tanggal_pembayaran"
                   value="{{ old('tanggal_pembayaran', date('Y-m-d')) }}">
        </div>

        <br>

        <div>
            <label>Nominal Pembayaran</label><br>
            <input type="number" name="nominal"
                   value="{{ old('nominal') }}"
                   min="0"
                   placeholder="Contoh: 500000">
        </div>

        <br>

        <button type="submit">Simpan Pembayaran</button>

        <a href="{{ route('pembayaran.index') }}">
            Kembali
        </a>
    </form>

</body>
</html>