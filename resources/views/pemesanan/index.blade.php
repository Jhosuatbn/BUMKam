<!DOCTYPE html>
<html>
<head>
    <title>Data Pemesanan</title>
</head>
<body>

    <h1>Data Pemesanan</h1>

    @if (session('success'))
        <p style="color: green;">
            {{ session('success') }}
        </p>
    @endif

    <a href="{{ route('pemesanan.create') }}">
        + Tambah Pemesanan
    </a>

    <br><br>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID Pemesanan</th>
                <th>Pelanggan</th>
                <th>Tanggal</th>
                <th>Skema Pembayaran</th>
                <th>Total Harga</th>
                <th>Angsuran</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($pemesanan as $item)
                <tr>
                    <td>{{ $item->id_pemesanan }}</td>

                    <td>
                        {{ $item->pelanggan->nama_pelanggan ?? '-' }}
                    </td>

                    <td>
                        {{ $item->tanggal_pemesanan }}
                    </td>

                    <td>
                        {{ $item->skema_pembayaran }}
                    </td>

                    <td>
                        Rp {{ number_format($item->total_harga, 0, ',', '.') }}
                    </td>

                    <td>
                        Rp {{ number_format($item->angsuran, 0, ',', '.') }}
                    </td>

                    <td>
                        {{ $item->status_pemesanan }}
                    </td>

                    <td>
                        <a href="{{ route('pemesanan.show', $item->id_pemesanan) }}">
                            Detail
                        </a>

                        |

                        <a href="{{ route('pemesanan.edit', $item->id_pemesanan) }}">
                            Edit
                        </a>

                        |

                        <form
                            action="{{ route('pemesanan.destroy', $item->id_pemesanan) }}"
                            method="POST"
                            style="display:inline;"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                onclick="return confirm('Yakin ingin menghapus pemesanan ini?')"
                            >
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        Belum ada data pemesanan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>