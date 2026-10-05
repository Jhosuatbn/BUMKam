<!DOCTYPE html>
<html>
<head>
    <title>Data Barang</title>
</head>
<body>

    <h1>Data Barang</h1>

    @if (session('success'))
        <p style="color: green;">
            {{ session('success') }}
        </p>
    @endif

    <a href="{{ route('barang.create') }}">
        + Tambah Barang
    </a>

    <br><br>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID Barang</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($barang as $item)
                <tr>
                    <td>{{ $item->id_barang }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->kategori }}</td>
                    <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>

                    <td>
                        <a href="{{ route('barang.show', $item->id_barang) }}">
                            Detail
                        </a>

                        |

                        <a href="{{ route('barang.edit', $item->id_barang) }}">
                            Edit
                        </a>

                        |

                        <form action="{{ route('barang.destroy', $item->id_barang) }}"
                              method="POST"
                              style="display:inline;">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    onclick="return confirm('Yakin ingin menghapus barang ini?')">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        Belum ada data barang.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>