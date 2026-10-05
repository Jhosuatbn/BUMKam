<!DOCTYPE html>
<html>
<head>
    <title>Detail Pemesanan</title>
</head>
<body>

    <h1>Detail Pemesanan</h1>

    <h3>Informasi Pemesanan</h3>

    <p>
        <strong>ID Pemesanan:</strong>
        {{ $pemesanan->id_pemesanan }}
    </p>

    <p>
        <strong>Pelanggan:</strong>
        {{ $pemesanan->pelanggan->nama_pelanggan ?? '-' }}
    </p>

    <p>
        <strong>Tanggal:</strong>
        {{ $pemesanan->tanggal_pemesanan }}
    </p>

    <p>
        <strong>Skema Pembayaran:</strong>
        {{ $pemesanan->skema_pembayaran }}
    </p>

    <p>
        <strong>DP:</strong>
        Rp {{ number_format($pemesanan->dp, 0, ',', '.') }}
    </p>

    <p>
        <strong>Tenor:</strong>
        {{ $pemesanan->tenor }} bulan
    </p>

    <p>
        <strong>Jaminan:</strong>
        {{ $pemesanan->jaminan ?? '-' }}
    </p>

    <p>
        <strong>Bunga:</strong>
        Rp {{ number_format($pemesanan->bunga, 0, ',', '.') }}
    </p>

    <p>
        <strong>Total Harga:</strong>
        Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}
    </p>

    <p>
        <strong>Angsuran:</strong>
        Rp {{ number_format($pemesanan->angsuran, 0, ',', '.') }}
    </p>

    <p>
        <strong>Status:</strong>
        {{ $pemesanan->status_pemesanan }}
    </p>

    <hr>

    <h3>Barang yang Dipesan</h3>

    @forelse ($pemesanan->detailPemesanan as $detail)
        <p>
            <strong>Barang:</strong>
            {{ $detail->barang->nama_barang ?? '-' }}
        </p>

        <p>
            <strong>Harga:</strong>
            Rp {{ number_format($detail->harga, 0, ',', '.') }}
        </p>

        <p>
            <strong>Jumlah:</strong>
            {{ $detail->jumlah }}
        </p>

        <p>
            <strong>Subtotal:</strong>
            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
        </p>
    @empty
        <p>Belum ada detail barang.</p>
    @endforelse

    <br>

    <a href="{{ route('pemesanan.edit', $pemesanan->id_pemesanan) }}">
        Edit
    </a>

    |

    <a href="{{ route('pemesanan.index') }}">
        Kembali
    </a>

</body>
</html>