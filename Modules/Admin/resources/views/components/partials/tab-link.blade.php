@php
    /** @var string $href */
    /** @var bool $active */
    $active = $active ?? false;
@endphp

{{-- Preline underline-tab pattern: active tab gets semibold + primary text + bottom bar --}}
<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'hs-tab-active:font-semibold hs-tab-active:text-primary-active relative py-4 px-1 inline-flex items-center text-sm whitespace-nowrap text-muted-foreground-1 after:absolute after:-bottom-px after:inset-x-0 after:h-0.5 ' . ($active
       ? 'after:bg-primary-active text-primary-active font-semibold'
       : 'after:bg-transparent hover:text-primary-hover') . ' focus:outline-hidden focus:text-primary-focus transition']) }}
   @if ($active) aria-current="page" @endif>
    {{ $slot }}
</a>