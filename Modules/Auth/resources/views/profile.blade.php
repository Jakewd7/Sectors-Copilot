<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Profil & Preferensi Workspace</title>
</head>

<body>
    <h1>Pengaturan Workspace Profil</h1>

    @if (session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li style="color: red;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('workspace.profile.update') }}">
        @csrf
        @method('PUT')

        <div>
            <label for="name">Nama:</label><br>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <br>
        <div>
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>
        <br>
        <div>
            <label for="theme">Preferensi Tema:</label><br>
            <select name="theme" id="theme">
                <option value="system" {{ old('theme', $user->theme ?? '') === 'system' ? 'selected' : '' }}>System
                </option>
                <option value="light" {{ old('theme', $user->theme ?? '') === 'light' ? 'selected' : '' }}>Light</option>
                <option value="dark" {{ old('theme', $user->theme ?? '') === 'dark' ? 'selected' : '' }}>Dark</option>
            </select>
        </div>
        <br>
        <div>
            <label for="password">Kata Sandi Baru (Kosongkan jika tidak diubah):</label><br>
            <input type="password" id="password" name="password">
        </div>
        <br>
        <div>
            <label for="password_confirmation">Konfirmasi Kata Sandi Baru:</label><br>
            <input type="password" id="password_confirmation" name="password_confirmation">
        </div>
        <br>
        <button type="submit">Simpan Perubahan</button>
    </form>

    <hr>
    <h3>Data Workspace Terikat (Multi-Tenant Test)</h3>
    <p>User UUID: {{ $user->id }}</p>
    <p>Role Aktif: {{ $user->roles->pluck('name')->implode(', ') }}</p>
    <p>Jumlah Watchlists: {{ $user->watchlists->count() }}</p>
    <p>Jumlah Chat Sessions: {{ $user->chatSessions->count() }}</p>
</body>

</html>