<!DOCTYPE html>
<html>
<head>
    <title>Dashboard BUMKam</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f5f6fa;
        }

        .navbar {
            background-color: #1f4d3a;
            color: white;
            padding: 20px 40px;
        }

        .navbar h1 {
            margin: 0;
        }

        .container {
            padding: 30px 40px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .card h3 {
            margin-top: 0;
            color: #555;
        }

        .card .number {
            font-size: 30px;
            font-weight: bold;
            color: #1f4d3a;
        }

        .status {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
        }

        .menu {
            margin-top: 30px;
        }

        .menu a {
            display: inline-block;
            padding: 12px 18px;
            margin-right: 10px;
            margin-bottom: 10px;
            background-color: #1f4d3a;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .menu a:hover {
            background-color: #163a2c;
        }
    </style>
</head>

<body>

    <div class="navbar">
        <h1>Dashboard BUMKam</h1>
    </div>

    <div class="container">

        <h2>Ringkasan Data</h2>

        <div class="cards">

            <div class="card">
                <h3>Total Pelanggan</h3>
                <div class="number">
                    {{ $totalPelanggan }}
                </div>
            </div>

            <div class="card">
                <h3>Total Barang</h3>
                <div class="number">
                    {{ $totalBarang }}
                </div>
            </div>

            <div class="card">
                <h3>Total Pemesanan</h3>
                <div class="number">
                    {{ $totalPemesanan }}
                </div>
            </div>

            <div class="card">
                <h3>Total Pembayaran</h3>
                <div class="number">
                    {{ $totalPembayaran }}
                </div>
            </div>

        </div>

        <div class="status">

            <div class="card">
                <h3>Pemesanan Lunas</h3>
                <div class="number">
                    {{ $pemesananLunas }}
                </div>
            </div>

            <div class="card">
                <h3>Pemesanan Belum Lunas</h3>
                <div class="number">
                    {{ $pemesananBelumLunas }}
                </div>
            </div>

        </div>

        <div class="menu">

            <h2>Menu</h2>

            <a href="{{ route('pelanggan.index') }}">
                Pelanggan
            </a>

            <a href="{{ route('barang.index') }}">
                Barang
            </a>

            <a href="{{ route('pemesanan.index') }}">
                Pemesanan
            </a>

            <a href="{{ route('pembayaran.index') }}">
                Pembayaran
            </a>

        </div>

    </div>

</body>

</html>