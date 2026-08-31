<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail User</title>
</head>

<body>
    <h1>Detail Pengguna</h1>
    <a href="{{ route('admin.users.index') }}">&larr; Kembali ke Daftar</a>
    <hr>

    <p><strong>UUID:</strong> {{ $user->id }}</p>
    <p><strong>Nama:</strong> {{ $user->name }}</p>
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p><strong>Role:</strong> {{ $user->roles->pluck('name')->implode(', ') }}</p>
    <p><strong>Terdaftar Pada:</strong> {{ $user->created_at }}</p>

    <hr>
    <h3>Data Workspace Terkait</h3>
    <p>Jumlah Watchlists: {{ $user->watchlists->count() }}</p>
    <p>Jumlah Chat Sessions: {{ $user->chatSessions->count() }}</p>
</body>

</html>