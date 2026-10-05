<!DOCTYPE html>
<html>
<head>
    <title>Detail Pembayaran</title>
</head>
<body>

    <h1>Detail Pembayaran</h1>

    <p>
        <strong>ID Pembayaran:</strong>
        {{ $pembayaran->id_pembayaran }}
    </p>

    <p>
        <strong>ID Pemesanan:</strong>
        {{ $pembayaran->id_pemesanan }}
    </p>

    <p>
        <strong>Pelanggan:</strong>
        {{ $pembayaran->pemesanan->pelanggan->nama_pelanggan ?? '-' }}
    </p>

    <p>
        <strong>Tanggal Pembayaran:</strong>
        {{ $pembayaran->tanggal_pembayaran }}
    </p>

    <p>
        <strong>Nominal:</strong>
        Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}
    </p>

    <p>
        <strong>Total Pembayaran:</strong>
        Rp {{ number_format($pembayaran->total_pembayaran, 0, ',', '.') }}
    </p>

    <p>
        <strong>Sisa Pembayaran:</strong>
        Rp {{ number_format($pembayaran->sisa_pembayaran, 0, ',', '.') }}
    </p>

    <p>
        <strong>Status:</strong>
        {{ $pembayaran->status_pembayaran }}
    </p>

    <br>

    <a href="{{ route('pembayaran.index') }}">
        Kembali
    </a>

</body>
</html>