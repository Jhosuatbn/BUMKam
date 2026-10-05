<!DOCTYPE html>
<html>
<head>
    <title>Edit Pembayaran</title>
</head>
<body>

    <h1>Edit Pembayaran</h1>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('pembayaran.update', $pembayaran->id_pembayaran) }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label>ID Pembayaran</label><br>
            <input type="text"
                   value="{{ $pembayaran->id_pembayaran }}"
                   disabled>
        </div>

        <br>

        <div>
            <label>Pemesanan</label><br>

            <select name="id_pemesanan">
                @foreach($pemesanan as $item)
                    <option value="{{ $item->id_pemesanan }}"
                        {{ $pembayaran->id_pemesanan == $item->id_pemesanan ? 'selected' : '' }}>

                        {{ $item->id_pemesanan }}
                        -
                        {{ $item->pelanggan->nama_pelanggan ?? '-' }}
                        -
                        Rp {{ number_format($item->total_harga, 0, ',', '.') }}

                    </option>
                @endforeach
            </select>
        </div>

        <br>

        <div>
            <label>Tanggal Pembayaran</label><br>

            <input type="date"
                   name="tanggal_pembayaran"
                   value="{{ $pembayaran->tanggal_pembayaran }}">
        </div>

        <br>

        <div>
            <label>Nominal Pembayaran</label><br>

            <input type="number"
                   name="nominal"
                   value="{{ $pembayaran->nominal }}"
                   min="1">
        </div>

        <br>

        <button type="submit">
            Simpan Perubahan
        </button>

        <a href="{{ route('pembayaran.index') }}">
            Batal
        </a>

    </form>

</body>
</html>