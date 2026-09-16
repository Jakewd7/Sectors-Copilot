<x-app-shell title="Profile">
    <div class="max-w-4xl mx-auto px-6 py-8 space-y-6">

        <!-- Page heading -->
        <div>
            <h1 class="text-2xl font-bold text-foreground tracking-tight">Profile & Preferences</h1>
            <p class="text-sm text-muted-foreground-1 mt-1">Manage your account details and workspace theme preference.</p>
        </div>

        @if (session('status'))
            {{-- Preline alert (success) --}}
            <div class="flex items-center gap-x-3 rounded-lg border border-primary/30 bg-primary/10 px-4 py-3 text-sm text-primary-active"
                 role="alert">
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            {{-- Preline alert (danger) --}}
            <div class="rounded-lg border border-danger/30 bg-danger/10 px-4 py-3 text-sm text-danger" role="alert">
                <p class="font-medium mb-1">Please fix the following:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('workspace.profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Card: Account details -->
            <div class="bg-layer border border-layer-line rounded-xl p-6 space-y-4">
                <h2 class="text-sm font-semibold text-foreground">Account details</h2>

                <div>
                    <label for="name" class="block text-sm mb-2 text-foreground">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                           class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition">
                </div>

                <div>
                    <label for="email" class="block text-sm mb-2 text-foreground">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition">
                </div>

                <div>
                    <label for="theme" class="block text-sm mb-2 text-foreground">Theme preference</label>
                    <select name="theme" id="theme"
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border form-select rounded-lg text-sm text-foreground focus:border-primary-focus focus:ring-primary-focus transition">
                        <option value="system" @selected(old('theme', $user->theme ?? '') === 'system')>System</option>
                        <option value="light" @selected(old('theme', $user->theme ?? '') === 'light')>Light</option>
                        <option value="dark" @selected(old('theme', $user->theme ?? '') === 'dark')>Dark</option>
                    </select>
                    <p class="text-xs text-muted-foreground-1 mt-2">Saved with your account; the sidebar toggle switches the theme instantly.</p>
                </div>
            </div>

            <!-- Card: Change password -->
            <div class="bg-layer border border-layer-line rounded-xl p-6 space-y-4">
                <div>
                    <h2 class="text-sm font-semibold text-foreground">Change password</h2>
                    <p class="text-xs text-muted-foreground-1 mt-1">Leave both fields empty to keep your current password.</p>
                </div>

                <div>
                    <label for="password" class="block text-sm mb-2 text-foreground">New password</label>
                    <input type="password" id="password" name="password" autocomplete="new-password"
                           class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm mb-2 text-foreground">Confirm new password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                           class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus transition">
                    Save Changes
                </button>
            </div>
        </form>

        <!-- Card: Workspace data (read-only summary) -->
        <div class="bg-layer border border-layer-line rounded-xl p-6">
            <h2 class="text-sm font-semibold text-foreground mb-4">Workspace data</h2>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs text-muted-foreground-1">User ID</dt>
                    <dd class="font-mono text-foreground mt-0.5 break-all">{{ $user->id }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground-1">Active role</dt>
                    <dd class="text-foreground mt-0.5">{{ $user->roles->pluck('name')->implode(', ') ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground-1">Watchlists</dt>
                    <dd class="text-foreground mt-0.5">{{ $user->watchlists->count() }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground-1">Chat sessions</dt>
                    <dd class="text-foreground mt-0.5">{{ $user->chatSessions->count() }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-app-shell>
