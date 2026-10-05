<!DOCTYPE html>
<html>
<head>
    <title>Tambah Barang</title>
</head>
<body>

    <h1>Tambah Barang</h1>

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

    <form action="{{ route('barang.store') }}" method="POST">
        @csrf

        <div>
            <label>ID Barang</label><br>
            <input
                type="text"
                name="id_barang"
                value="{{ old('id_barang') }}"
                placeholder="Contoh: BRG001"
                required
            >
        </div>

        <br>

        <div>
            <label>Nama Barang</label><br>
            <input
                type="text"
                name="nama_barang"
                value="{{ old('nama_barang') }}"
                placeholder="Contoh: Kursi"
                required
            >
        </div>

        <br>

        <div>
            <label>Kategori</label><br>
            <input
                type="text"
                name="kategori"
                value="{{ old('kategori') }}"
                placeholder="Contoh: Furniture"
                required
            >
        </div>

        <br>

        <div>
            <label>Harga</label><br>
            <input
                type="number"
                name="harga"
                value="{{ old('harga') }}"
                min="0"
                placeholder="Contoh: 1500000"
                required
            >
        </div>

        <br>

        <button type="submit">Simpan</button>

        <a href="{{ route('barang.index') }}">
            Kembali
        </a>
    </form>

</body>
</html>