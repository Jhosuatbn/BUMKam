<!DOCTYPE html>
<html>
<head>
    <title>Detail Pengguna</title>
</head>
<body>

    <h1>Detail Pengguna</h1>

    <p>
        <strong>ID Pengguna:</strong>
        {{ $pengguna->id_pengguna }}
    </p>

    <p>
        <strong>Username:</strong>
        {{ $pengguna->username }}
    </p>

    <p>
        <strong>Nama Lengkap:</strong>
        {{ $pengguna->nama_lengkap }}
    </p>

    <p>
        <strong>Peran:</strong>
        {{ $pengguna->peran }}
    </p>

    <br>

    <a href="{{ route('pengguna.index') }}">
        Kembali
    </a>

</body>
</html>