{{-- File: resources/views/livewire/customer/partials/sailing-information.blade.php
     Responsibility: The sailing strip that heads the B/L detail card.
     What it does: renders the shipment's route (ports and their dates) as a
       line view; the vessel facts live in the shipment overview. Fed by
       ShipmentTimeline::sailingInformation(), so only values whose milestone
       has been reached appear — the strip itself hides when there is nothing.
     How to use: included at the top of the shared sailing/containers card on
       the customer B/L detail pages.
     How to extend: add labels to ShipmentTimeline::sailingInformation(). --}}
@php
    $pol = $sailing['Port of loading'] ?? null;
    $departure = $sailing['Departure date'] ?? null;
    $pod = $sailing['Port of discharge'] ?? null;
    $eta = $sailing['Arrival time / ETA'] ?? null;
    $hasOrigin = filled($pol) || filled($departure);
    $hasDestination = filled($pod) || filled($eta);
@endphp
@if ($sailing !== [])
<div class="px-4 py-4 sm:px-6">
    @if ($hasOrigin || $hasDestination)
        {{-- Route strip: a dashed line whose ends mark the ports, each port's
             facts sitting underneath its end. The ports stay side by side on
             mobile (two columns) so the dots keep lining up with their text. --}}
        <div>
            <div class="relative mt-1 h-3" aria-hidden="true">
                <span class="absolute inset-x-0 top-1/2 -translate-y-1/2 border-t-2 border-dashed border-brand-400"></span>
                @if ($hasOrigin)
                    <span class="absolute left-0 top-1/2 h-3 w-3 -translate-y-1/2 rounded-full border-2 border-brand-500 bg-white"></span>
                @endif
                @if ($hasDestination)
                    <span class="absolute right-0 top-1/2 h-3 w-3 -translate-y-1/2 rounded-full bg-brand-500"></span>
                @endif
            </div>
            <div class="mt-2 grid grid-cols-2 gap-3 sm:flex sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                @if ($hasOrigin)
                    <div class="min-w-0">
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Port of loading</div>
                        <div class="mt-1 break-words text-sm font-medium text-slate-900 sm:text-base">{{ $pol ?? '—' }}</div>
                        @if (filled($departure))
                            <div class="mt-0.5 text-xs text-slate-500">Departure date · {{ $departure }}</div>
                        @endif
                    </div>
                @endif
                @if ($hasDestination)
                    <div @class(['min-w-0 sm:text-right', 'col-start-2' => ! $hasOrigin])>
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Port of discharge</div>
                        <div class="mt-1 break-words text-sm font-medium text-slate-900 sm:text-base">{{ $pod ?? '—' }}</div>
                        @if (filled($eta))
                            <div class="mt-0.5 text-xs text-slate-500">Arrival time / ETA · {{ $eta }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endif
