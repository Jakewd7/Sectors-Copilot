<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign Up - {{ config('app.name', 'Sectors Copilot') }}</title>

    @fonts
    @vite(['resources/css/app.css'])
</head>

<body class="bg-base text-text-primary font-sans antialiased flex items-center justify-center min-h-screen p-6">
    <div class="w-full max-w-sm">
        @include('auth::components.auth-logo', ['class' => 'mx-auto mb-8'])

        <h1 class="text-2xl font-semibold text-center text-text-primary mb-8">Sign Up</h1>

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/30 text-danger text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-text-primary mb-2">Full name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                    placeholder="Enter your full name"
                    class="w-full bg-surface border border-border rounded-lg px-4 py-3 text-sm text-text-primary placeholder-text-muted/60 focus:outline-none focus:border-accent-dim focus:ring-2 focus:ring-accent/20 transition">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-text-primary mb-2">Email address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                    placeholder="Enter your email address"
                    class="w-full bg-surface border border-border rounded-lg px-4 py-3 text-sm text-text-primary placeholder-text-muted/60 focus:outline-none focus:border-accent-dim focus:ring-2 focus:ring-accent/20 transition">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-text-primary mb-2">Password</label>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                    placeholder="Create a password"
                    class="w-full bg-surface border border-border rounded-lg px-4 py-3 text-sm text-text-primary placeholder-text-muted/60 focus:outline-none focus:border-accent-dim focus:ring-2 focus:ring-accent/20 transition">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-text-primary mb-2">Confirm
                    password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    autocomplete="new-password" placeholder="Repeat your password"
                    class="w-full bg-surface border border-border rounded-lg px-4 py-3 text-sm text-text-primary placeholder-text-muted/60 focus:outline-none focus:border-accent-dim focus:ring-2 focus:ring-accent/20 transition">
            </div>

            <!-- Captcha placeholder (display only, no backend verification yet) -->
            @include('auth::components.auth-captcha-placeholder')

            <button type="submit"
                class="w-full py-3 px-4 bg-accent hover:bg-accent-dim text-base font-semibold rounded-lg transition focus:outline-none focus:ring-2 focus:ring-accent/40">
                Continue
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-text-muted">
            Already have an account?
            <a href="{{ route('login') }}"
                class="font-semibold text-text-primary hover:text-accent underline underline-offset-4">Sign in</a>
        </p>
    </div>
</body>

</html>
