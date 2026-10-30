@php
    $brandLogoLight = \App\Support\Settings::logoUrl('light');
    $brandLogoDark = \App\Support\Settings::logoUrl('dark');
@endphp
@if ($brandLogoLight || $brandLogoDark)
    <img class="brand-logo brand-logo-light" src="{{ $brandLogoLight }}" alt="{{ config('app.name') }}">
    <img class="brand-logo brand-logo-dark" src="{{ $brandLogoDark }}" alt="{{ config('app.name') }}">
@else
    <span class="brand-mark">P</span>
    <span>
        PenakeTik
        @if (filled($subtitle ?? null))
            <small>{{ $subtitle }}</small>
        @endif
    </span>
@endif
