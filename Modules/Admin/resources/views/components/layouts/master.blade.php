{{-- Admin panel layout = global app shell + the tab bar as SUB-NAVIGATION (option B).
     The sidebar handles global navigation; this top bar stays for fast hops
     between the five admin sub-pages. --}}
<x-app-shell>
    <x-slot:navigation>
        {{ $navigation ?? '' }}
    </x-slot:navigation>

    <div class="max-w-6xl mx-auto px-6 py-8">
        {{ $slot }}
    </div>
</x-app-shell>
