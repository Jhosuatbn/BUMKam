<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan</title>
</head>
<body>

    <h1>Data Pelanggan</h1>

    @if(session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <a href="{{ route('pelanggan.create') }}">
        Tambah Pelanggan
    </a>

    <br><br>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID Pelanggan</th>
                <th>Nama</th>
                <th>NIK</th>
                <th>No. Telepon</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($pelanggan as $p)
                <tr>
                    <td>{{ $p->id_pelanggan }}</td>
                    <td>{{ $p->nama_pelanggan }}</td>
                    <td>{{ $p->nik }}</td>
                    <td>{{ $p->no_telepon }}</td>
                    <td>{{ $p->alamat }}</td>
                    <td>
                        <a href="{{ route('pelanggan.show', $p->id_pelanggan) }}">
                            Detail
                        </a>

                        <a href="{{ route('pelanggan.edit', $p->id_pelanggan) }}">
                            Edit
                        </a>

                        <form action="{{ route('pelanggan.destroy', $p->id_pelanggan) }}"
                              method="POST"
                              style="display:inline;">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    onclick="return confirm('Yakin ingin menghapus data ini?')">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        Belum ada data pelanggan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>