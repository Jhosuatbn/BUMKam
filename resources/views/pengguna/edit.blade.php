<!DOCTYPE html>
<html>
<head>
    <title>Edit Pengguna</title>
</head>
<body>

    <h1>Edit Pengguna</h1>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('pengguna.update', $pengguna->id_pengguna) }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label>ID Pengguna</label><br>
            <input type="text"
                   value="{{ $pengguna->id_pengguna }}"
                   disabled>
        </div>

        <br>

        <div>
            <label>Username</label><br>
            <input type="text"
                   name="username"
                   value="{{ old('username', $pengguna->username) }}">
        </div>

        <br>

        <div>
            <label>Password Baru</label><br>
            <input type="password"
                   name="password"
                   placeholder="Kosongkan jika tidak ingin mengubah password">
        </div>

        <br>

        <div>
            <label>Nama Lengkap</label><br>
            <input type="text"
                   name="nama_lengkap"
                   value="{{ old('nama_lengkap', $pengguna->nama_lengkap) }}">
        </div>

        <br>

        <div>
            <label>Peran</label><br>

            <select name="peran">
                <option value="Staff"
                    {{ $pengguna->peran == 'Staff' ? 'selected' : '' }}>
                    Staff
                </option>

                <option value="Pimpinan"
                    {{ $pengguna->peran == 'Pimpinan' ? 'selected' : '' }}>
                    Pimpinan
                </option>
            </select>
        </div>

        <br>

        <button type="submit">
            Simpan Perubahan
        </button>

        <a href="{{ route('pengguna.index') }}">
            Batal
        </a>
    </form>

</body>
</html>