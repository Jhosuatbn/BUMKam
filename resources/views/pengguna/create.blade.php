<!DOCTYPE html>
<html>
<head>
    <title>Tambah Pengguna</title>
</head>
<body>

    <h1>Tambah Pengguna</h1>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('pengguna.store') }}" method="POST">
        @csrf

        <div>
            <label>ID Pengguna</label><br>
            <input type="text"
                   name="id_pengguna"
                   value="{{ old('id_pengguna') }}"
                   placeholder="Contoh: PGN-001">
        </div>

        <br>

        <div>
            <label>Username</label><br>
            <input type="text"
                   name="username"
                   value="{{ old('username') }}"
                   placeholder="Masukkan username">
        </div>

        <br>

        <div>
            <label>Password</label><br>
            <input type="password"
                   name="password"
                   placeholder="Masukkan password">
        </div>

        <br>

        <div>
            <label>Nama Lengkap</label><br>
            <input type="text"
                   name="nama_lengkap"
                   value="{{ old('nama_lengkap') }}"
                   placeholder="Masukkan nama lengkap">
        </div>

        <br>

        <div>
            <label>Peran</label><br>

            <select name="peran">
                <option value="">-- Pilih Peran --</option>

                <option value="Staff"
                    {{ old('peran') == 'Staff' ? 'selected' : '' }}>
                    Staff
                </option>

                <option value="Pimpinan"
                    {{ old('peran') == 'Pimpinan' ? 'selected' : '' }}>
                    Pimpinan
                </option>
            </select>
        </div>

        <br>

        <button type="submit">
            Simpan Pengguna
        </button>

        <a href="{{ route('pengguna.index') }}">
            Kembali
        </a>
    </form>

</body>
</html>