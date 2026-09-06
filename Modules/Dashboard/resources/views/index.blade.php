@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-6 py-12">
        <h1 class="text-2xl font-semibold text-text-primary tracking-tight">Dashboard</h1>
        <p class="text-sm text-text-muted mt-1">Welcome back{{ auth()->check() ? ', ' . auth()->user()->name : '' }}.</p>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mt-8">
            <!-- Chat Workspace -->
            <a href="{{ route('agent.workspace') }}"
               class="group bg-surface border border-border rounded-2xl p-6 hover:border-accent-dim transition">
                <div class="w-11 h-11 rounded-xl bg-accent/15 border border-accent/30 text-accent flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <h2 class="font-semibold text-text-primary group-hover:text-accent transition">Chat Workspace</h2>
                <p class="text-sm text-text-muted mt-1">Research equities with the AI copilot.</p>
            </a>

            <!-- Admin Panel -->
            <a href="{{ route('admin.users.index') }}"
               class="group bg-surface border border-border rounded-2xl p-6 hover:border-accent-dim transition">
                <div class="w-11 h-11 rounded-xl bg-accent/15 border border-accent/30 text-accent flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.427 1.756-2.925 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h2 class="font-semibold text-text-primary group-hover:text-accent transition">Admin Panel</h2>
                <p class="text-sm text-text-muted mt-1">Users, market articles, prompts & cache.</p>
            </a>

            <!-- Profile -->
            <a href="{{ route('workspace.profile') }}"
               class="group bg-surface border border-border rounded-2xl p-6 hover:border-accent-dim transition">
                <div class="w-11 h-11 rounded-xl bg-accent/15 border border-accent/30 text-accent flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h2 class="font-semibold text-text-primary group-hover:text-accent transition">Profile</h2>
                <p class="text-sm text-text-muted mt-1">Account settings & watchlists.</p>
            </a>
        </div>
    </div>
@endsection