<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>

<body>
    <h1>Masuk Akun</h1>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li style="color: red;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div>
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <br>
        <div>
            <label for="password">Kata Sandi:</label><br>
            <input type="password" id="password" name="password" required>
        </div>
        <br>
        <div>
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Ingat Saya</label>
        </div>
        <br>
        <button type="submit">Masuk</button>
    </form>
    <br>
    <a href="{{ route('register') }}">Belum punya akun? Daftar di sini</a>
</body>

</html>