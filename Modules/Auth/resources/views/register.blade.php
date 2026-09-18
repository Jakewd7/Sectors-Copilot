@php
    $theme = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light' ? 'light' : 'dark';
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full {{ $theme === 'dark' ? 'dark' : 'light' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign Up - {{ config('app.name', 'Sectors Copilot') }}</title>

    @include('partials.prepaint-script')

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-base text-text-primary font-sans antialiased flex items-center justify-center min-h-screen p-6">

    <button type="button" onclick="toggleTheme()" aria-label="Toggle theme" title="Toggle light/dark theme"
            class="fixed top-5 right-5 w-10 h-10 rounded-xl bg-surface border border-border text-text-muted hover:text-text-primary transition inline-flex items-center justify-center">

        <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>

        <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
    </button>

    <div class="w-full max-w-sm">

        @include('auth::components.auth-logo', ['class' => 'mx-auto mb-8'])

        <div class="bg-card border border-card-line rounded-xl shadow-xl shadow-black/10 dark:shadow-black/40">
            <div class="p-4 sm:p-7">
                <div class="text-center">
                    <h3 class="block text-2xl font-bold text-foreground">Sign up</h3>
                    <p class="mt-2 text-sm text-muted-foreground-2">
                        Already have an account?
                        <a class="text-primary decoration-2 hover:underline focus:outline-hidden focus:underline font-medium"
                           href="{{ route('login') }}">Sign in here</a>
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mt-4 p-3 rounded-lg bg-danger/10 border border-danger/30 text-danger text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="mt-5">

                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="grid gap-y-4">

                            <div>
                                <label for="name" class="block text-sm mb-2 text-foreground">Full name</label>
                                <div class="relative">
                                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                                           class="py-2.5 sm:py-3 px-4 block w-full bg-auth-field form-field-border rounded-lg sm:text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none"
                                           required autofocus placeholder="Enter your full name">
                                </div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm mb-2 text-foreground">Email address</label>
                                <div class="relative">
                                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                                           class="py-2.5 sm:py-3 px-4 block w-full bg-auth-field form-field-border rounded-lg sm:text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none"
                                           required placeholder="Enter your email address">
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-sm mb-2 text-foreground">Password</label>
                                <div class="relative">
                                    <input type="password" id="password" name="password"
                                           class="py-2.5 sm:py-3 px-4 block w-full bg-auth-field form-field-border rounded-lg sm:text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none"
                                           required autocomplete="new-password" placeholder="Create a password">
                                </div>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm mb-2 text-foreground">Confirm Password</label>
                                <div class="relative">
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                           class="py-2.5 sm:py-3 px-4 block w-full bg-auth-field form-field-border rounded-lg sm:text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none"
                                           required autocomplete="new-password" placeholder="Repeat your password">
                                </div>
                            </div>

                            <div class="flex items-center">
                                <div class="flex">
                                    <input id="terms" name="terms" type="checkbox" required
                                           class="shrink-0 size-4 bg-transparent border-line-3 rounded-sm shadow-2xs text-primary focus:ring-0 focus:ring-offset-0 checked:bg-primary-checked checked:border-primary-checked disabled:opacity-50 disabled:pointer-events-none">
                                </div>
                                <div class="ms-3">
                                    <label for="terms" class="text-sm text-foreground">
                                        I accept the <a href="#" class="text-primary decoration-2 hover:underline focus:outline-hidden focus:underline font-medium">Terms and Conditions</a>
                                    </label>
                                </div>
                            </div>

                            @include('auth::components.auth-captcha-placeholder')

                            <button type="submit"
                                    class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus disabled:opacity-50 disabled:pointer-events-none">Sign up</button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</body>

</html>