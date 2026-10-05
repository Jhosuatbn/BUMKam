<!DOCTYPE html>
<html>
<head>
    <title>Edit Pelanggan</title>
</head>
<body>

    <h1>Edit Pelanggan</h1>

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

    <form action="{{ route('pelanggan.update', $pelanggan->id_pelanggan) }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label>ID Pelanggan</label><br>
            <input type="text"
                   value="{{ $pelanggan->id_pelanggan }}"
                   disabled>
        </div>

        <br>

        <div>
            <label>Nama Pelanggan</label><br>
            <input type="text"
                   name="nama_pelanggan"
                   value="{{ old('nama_pelanggan', $pelanggan->nama_pelanggan) }}"
                   required>
        </div>

        <br>

        <div>
            <label>NIK</label><br>
            <input type="text"
                   name="nik"
                   value="{{ old('nik', $pelanggan->nik) }}"
                   required>
        </div>

        <br>

        <div>
            <label>No. Telepon</label><br>
            <input type="text"
                   name="no_telepon"
                   value="{{ old('no_telepon', $pelanggan->no_telepon) }}"
                   required>
        </div>

        <br>

        <div>
            <label>Alamat</label><br>
            <textarea name="alamat" required>{{ old('alamat', $pelanggan->alamat) }}</textarea>
        </div>

        <br>

        <button type="submit">Update</button>
        <a href="{{ route('pelanggan.index') }}">Kembali</a>
    </form>

</body>
</html>