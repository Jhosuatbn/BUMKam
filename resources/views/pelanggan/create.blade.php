<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pelanggan</title>
</head>
<body>

    <h1>Tambah Pelanggan</h1>

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

    <form action="{{ route('pelanggan.store') }}" method="POST">

        @csrf

        <div>
            <label>ID Pelanggan</label>
            <input type="text"
                   name="id_pelanggan"
                   value="{{ old('id_pelanggan') }}"
                   required>
        </div>

        <br>

        <div>
            <label>Nama Pelanggan</label>
            <input type="text"
                   name="nama_pelanggan"
                   value="{{ old('nama_pelanggan') }}"
                   required>
        </div>

        <br>

        <div>
            <label>NIK</label>
            <input type="text"
                   name="nik"
                   value="{{ old('nik') }}"
                   required>
        </div>

        <br>

        <div>
            <label>No. Telepon</label>
            <input type="text"
                   name="no_telepon"
                   value="{{ old('no_telepon') }}"
                   required>
        </div>

        <br>

        <div>
            <label>Alamat</label>
            <textarea name="alamat" required>{{ old('alamat') }}</textarea>
        </div>

        <br>

        <button type="submit">
            Simpan
        </button>

        <a href="{{ route('pelanggan.index') }}">
            Kembali
        </a>

    </form>

</body>
</html>