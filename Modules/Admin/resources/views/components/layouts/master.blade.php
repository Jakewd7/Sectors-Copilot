<x-app-shell>
    <x-slot:navigation>
        {{ $navigation ?? '' }}
    </x-slot:navigation>

    <div class="max-w-6xl mx-auto px-6 py-8">
        {{ $slot }}
    </div>
</x-app-shell>