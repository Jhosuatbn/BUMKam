<!DOCTYPE html>
<html>
<head>
    <title>Data Pengguna</title>
</head>
<body>

    <h1>Data Pengguna</h1>

    @if(session('success'))
        <p style="color: green;">
            {{ session('success') }}
        </p>
    @endif

    <a href="{{ route('pengguna.create') }}">
        + Tambah Pengguna
    </a>

    <br><br>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID Pengguna</th>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Peran</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($pengguna as $item)
                <tr>
                    <td>{{ $item->id_pengguna }}</td>
                    <td>{{ $item->username }}</td>
                    <td>{{ $item->nama_lengkap }}</td>
                    <td>{{ $item->peran }}</td>

                    <td>
                        <a href="{{ route('pengguna.show', $item->id_pengguna) }}">
                            Detail
                        </a>
                        <a href="{{ route('pengguna.edit', $item->id_pengguna) }}">
                            Edit
                        </a>
                        <form action="{{ route('pengguna.destroy', $item->id_pengguna) }}"
      method="POST"
      style="display:inline;">
    @csrf
    @method('DELETE')

    <button type="submit"
            onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
        Hapus
    </button>
</form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        Belum ada data pengguna.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>