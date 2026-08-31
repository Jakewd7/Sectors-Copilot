<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Manajemen User</title>
</head>

<body>
    <h1>Daftar Pengguna</h1>
    <a href="{{ route('admin.users.create') }}">+ Tambah Pengguna Baru</a>
    <hr>

    @if (session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <p style="color: red;">{{ $errors->first() }}</p>
    @endif

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>UUID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->email }}</td>
                    <td>{{ $item->roles->pluck('name')->implode(', ') }}</td>
                    <td>
                        <a href="{{ route('admin.users.show', $item->id) }}">Detail</a> |
                        <a href="{{ route('admin.users.edit', $item->id) }}">Edit</a> |
                        <form action="{{ route('admin.users.destroy', $item->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Hapus user ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada data user.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <br>
    {{ $users->links() }}
</body>

</html>