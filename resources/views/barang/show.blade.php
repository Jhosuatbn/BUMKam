<!DOCTYPE html>
<html>
<head>
    <title>Edit Barang</title>
</head>
<body>

    <h1>Edit Barang</h1>

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

    <form action="{{ route('barang.update', $barang->id_barang) }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label>ID Barang</label><br>
            <input type="text"
                   value="{{ $barang->id_barang }}"
                   disabled>
        </div>

        <br>

        <div>
            <label>Nama Barang</label><br>
            <input type="text"
                   name="nama_barang"
                   value="{{ old('nama_barang', $barang->nama_barang) }}"
                   required>
        </div>

        <br>

        <div>
            <label>Kategori</label><br>
            <input type="text"
                   name="kategori"
                   value="{{ old('kategori', $barang->kategori) }}"
                   required>
        </div>

        <br>

        <div>
            <label>Harga</label><br>
            <input type="number"
                   name="harga"
                   value="{{ old('harga', $barang->harga) }}"
                   min="0"
                   required>
        </div>

        <br>

        <button type="submit">Update</button>

        <a href="{{ route('barang.index') }}">
            Kembali
        </a>
    </form>

</body>
</html>