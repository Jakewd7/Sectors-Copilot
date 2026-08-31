<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ isset($user) ? 'Edit Pengguna' : 'Tambah Pengguna' }}</title>
</head>

<body>
    <h1>{{ isset($user) ? 'Edit Pengguna: ' . $user->name : 'Tambah Pengguna Baru' }}</h1>
    <a href="{{ route('admin.users.index') }}">&larr; Kembali ke Daftar</a>
    <hr>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li style="color: red;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
        action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}">
        @csrf
        @if (isset($user))
            @method('PUT')
        @endif

        <div>
            <label for="name">Nama Lengkap:</label><br>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required>
        </div>
        <br>
        <div>
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
        </div>
        <br>
        <div>
            <label for="password">Kata Sandi {{ isset($user) ? '(Kosongkan jika tidak diganti)' : '' }}:</label><br>
            <input type="password" id="password" name="password" {{ isset($user) ? '' : 'required' }}>
        </div>
        <br>
        <div>
            <label for="role">Role:</label><br>
            <select name="role" id="role" required>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" {{ (old('role', isset($user) ? $user->roles->first()?->name : '') === $role) ? 'selected' : '' }}>
                        {{ $role }}
                    </option>
                @endforeach
            </select>
        </div>
        <br>
        <button type="submit">{{ isset($user) ? 'Perbarui Data User' : 'Simpan User' }}</button>
    </form>
</body>

</html>