{{-- File: resources/views/livewire/customer/partials/sailing-information.blade.php
     Responsibility: The sailing card on the customer B/L detail pages.
     What it does: renders the shipment's sailing facts (ports, dates, vessel,
       voyage, shipping line) as a route view; fed by
       ShipmentTimeline::sailingInformation(), so only values whose milestone
       has been reached appear — the card itself hides when there is nothing.
     How to use: @include('livewire.customer.partials.sailing-information', ['sailing' => $sailing]).
     How to extend: add labels to ShipmentTimeline::sailingInformation(). --}}
@php
    $pol = $sailing['Port of loading'] ?? null;
    $departure = $sailing['Departure date'] ?? null;
    $pod = $sailing['Port of discharge'] ?? null;
    $actualArrival = $sailing['Actual arrival'] ?? null;
    $eta = $sailing['Arrival time / ETA'] ?? null;
    $hasOrigin = filled($pol) || filled($departure);
    $hasDestination = filled($pod) || filled($actualArrival) || filled($eta);
    $vesselFacts = array_filter([
        'Vessel name' => $sailing['Vessel name'] ?? null,
        'Voyage number' => $sailing['Voyage number'] ?? null,
        'Shipping line' => $sailing['Shipping line'] ?? null,
    ], filled(...));
@endphp
@if ($sailing !== [])
<div class="mt-6 rounded-xl bg-white p-6 shadow-sm">
    <h2 class="font-semibold text-slate-900">Sailing information</h2>
    @if ($hasOrigin || $hasDestination)
        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-stretch">
            @if ($hasOrigin)
                <div class="flex-1 rounded-lg bg-brand-50 p-4 ring-1 ring-brand-100">
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Port of loading</div>
                    <div class="mt-1 font-medium text-slate-900">{{ $pol ?? '—' }}</div>
                    @if (filled($departure))
                        <div class="mt-1 text-xs text-slate-500">Departure date · {{ $departure }}</div>
                    @endif
                </div>
            @endif
            @if ($hasOrigin && $hasDestination)
                <div class="flex items-center justify-center text-slate-400" aria-hidden="true">
                    <svg class="h-5 w-5 rotate-90 sm:rotate-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h13m0 0-4-4m4 4-4 4" />
                    </svg>
                </div>
            @endif
            @if ($hasDestination)
                <div class="flex-1 rounded-lg bg-accent-100/50 p-4 ring-1 ring-accent-100">
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Port of discharge</div>
                    <div class="mt-1 font-medium text-slate-900">{{ $pod ?? '—' }}</div>
                    @if (filled($actualArrival))
                        <div class="mt-1 text-xs text-slate-500">Actual arrival · {{ $actualArrival }}</div>
                    @elseif (filled($eta))
                        <div class="mt-1 text-xs text-slate-500">Arrival time / ETA · {{ $eta }}</div>
                    @endif
                </div>
            @endif
        </div>
    @endif
    @if ($vesselFacts !== [])
        <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-3">
            @foreach ($vesselFacts as $label => $value)
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    @endif
</div>
@endif
