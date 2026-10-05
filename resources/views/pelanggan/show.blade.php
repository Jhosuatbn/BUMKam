<!DOCTYPE html>
<html>
<head>
    <title>Detail Pelanggan</title>
</head>
<body>

    <h1>Detail Pelanggan</h1>

    <p>
        <strong>ID Pelanggan:</strong>
        {{ $pelanggan->id_pelanggan }}
    </p>

    <p>
        <strong>Nama Pelanggan:</strong>
        {{ $pelanggan->nama_pelanggan }}
    </p>

    <p>
        <strong>NIK:</strong>
        {{ $pelanggan->nik }}
    </p>

    <p>
        <strong>No. Telepon:</strong>
        {{ $pelanggan->no_telepon }}
    </p>

    <p>
        <strong>Alamat:</strong>
        {{ $pelanggan->alamat }}
    </p>

    <br>

    <a href="{{ route('pelanggan.edit', $pelanggan->id_pelanggan) }}">
        Edit
    </a>

    <a href="{{ route('pelanggan.index') }}">
        Kembali
    </a>

</body>
</html>