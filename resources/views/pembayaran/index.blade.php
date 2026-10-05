<!DOCTYPE html>
<html>
<head>
    <title>Data Pembayaran</title>
</head>
<body>

    <h1>Data Pembayaran</h1>

    @if(session('success'))
        <p style="color: green;">
            {{ session('success') }}
        </p>
    @endif

    <a href="{{ route('pembayaran.create') }}">
        + Tambah Pembayaran
    </a>

    <br><br>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID Pembayaran</th>
                <th>ID Pemesanan</th>
                <th>Pelanggan</th>
                <th>Tanggal Pembayaran</th>
                <th>Nominal</th>
                <th>Total Pembayaran</th>
                <th>Sisa Pembayaran</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($pembayaran as $item)
                <tr>
                    <td>{{ $item->id_pembayaran }}</td>

                    <td>{{ $item->id_pemesanan }}</td>

                    <td>
                        {{ $item->pemesanan->pelanggan->nama_pelanggan ?? '-' }}
                    </td>

                    <td>{{ $item->tanggal_pembayaran }}</td>

                    <td>
                        Rp {{ number_format($item->nominal, 0, ',', '.') }}
                    </td>

                    <td>
                        Rp {{ number_format($item->total_pembayaran, 0, ',', '.') }}
                    </td>

                    <td>
                        Rp {{ number_format($item->sisa_pembayaran, 0, ',', '.') }}
                    </td>

                    <td>{{ $item->status_pembayaran }}</td>

                    <td>
                        <a href="{{ route('pembayaran.show', $item->id_pembayaran) }}">
                            Detail
                        </a>
                        <a href="{{ route('pembayaran.edit', $item->id_pembayaran) }}">
                            Edit
                        </a>
                        <form action="{{ route('pembayaran.destroy', $item->id_pembayaran) }}"
      method="POST"
      style="display:inline;">
    @csrf
    @method('DELETE')

    <button type="submit"
            onclick="return confirm('Yakin ingin menghapus pembayaran ini?')">
        Hapus
    </button>
</form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        Belum ada data pembayaran.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>