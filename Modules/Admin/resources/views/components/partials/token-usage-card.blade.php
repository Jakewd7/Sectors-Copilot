@php
    $title = $title ?? '';
    $subtitle = $subtitle ?? '';
    $percent = max(0.0, min(100.0, (float) ($percent ?? 0)));
    $percentLabel = $percentLabel ?? round($percent, 1) . '% used';
    $meta = $meta ?? '';
    $models = $models ?? [];
@endphp

<div class="bg-layer border border-layer-line rounded-xl p-5 w-full">
    <h3 class="text-sm font-semibold text-foreground">{{ $title }}</h3>
    @if ($subtitle)
        <p class="text-xs text-muted-foreground-1 mt-1">{{ $subtitle }}</p>
    @endif

    <div class="flex items-center justify-between mt-4">
        <span class="text-sm font-medium text-foreground">{{ $label ?? '' }}</span>
        <span class="text-sm text-muted-foreground-1">{{ $percentLabel }}</span>
    </div>

    <div class="mt-1.5 w-full h-2.5 rounded-full bg-surface-4 overflow-hidden flex" role="progressbar"
         aria-valuenow="{{ (int) round($percent) }}" aria-valuemin="0" aria-valuemax="100">
        @foreach ($models as $model)
            <div style="width: {{ $model['share'] }}%; background-color: {{ $model['color'] }};"></div>
        @endforeach
    </div>

    @if ($meta)
        <p class="text-xs text-muted-foreground-1 mt-1.5">{{ $meta }}</p>
    @endif

    @if ($models)
        <div class="mt-4">
            <p class="text-xs text-muted-foreground-1 mb-2">Models used this month</p>
            <ul class="space-y-1.5">
                @foreach ($models as $model)
                    <li class="flex items-center justify-between gap-4 text-sm">
                        <span class="inline-flex items-center gap-x-2 text-foreground">
                            <span class="size-2.5 rounded-[4px] inline-block" style="background-color: {{ $model['color'] }}"></span>
                            {{ $model['name'] }}
                        </span>
                        <span class="text-muted-foreground-1">{{ $model['requests'] }} requests</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>