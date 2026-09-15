{{-- File: resources/views/components/brand-logo.blade.php
     Responsibility: Renders the company mark used across the customer portal.
     What it does: outputs the published logo (assets/logo.png -> public/images/logo.png)
       with an accessible alt text; size comes from the caller's class.
     How to use: <x-brand-logo class="h-10" />
     How to extend: swap the source here if a dark-mode variant is added. --}}
@props(['alt' => null])

<img
    src="{{ asset('images/logo.png') }}"
    alt="{{ $alt ?? config('app.name', 'Tracking') }}"
    {{ $attributes->merge(['class' => 'h-8 w-auto']) }}
>
