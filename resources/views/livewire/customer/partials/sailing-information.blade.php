{{-- File: resources/views/livewire/customer/partials/sailing-information.blade.php
     Responsibility: The sailing block shared by the container detail pages.
     What it does: shows the parent shipment's vessel, line, ports and dates.
     How to use: @include('livewire.customer.partials.sailing-information', ['shipment' => $container->shipment]).
     How to extend: add sailing fields as they become customer-visible. --}}
<div class="mt-6 rounded-xl bg-white p-6 shadow-sm">
    <h2 class="font-semibold text-slate-900">Sailing information</h2>
    <div class="mt-3 grid gap-4 text-sm md:grid-cols-2">
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Vessel</div>
            <div class="font-medium">
                {{ trim(($shipment->vessel_name ?? '').' '.($shipment->voyage_number ?? '')) ?: '—' }}
            </div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Shipping line</div>
            <div class="font-medium">{{ $shipment->shipping_line ?? '—' }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Port of loading / departure</div>
            <div class="font-medium">
                {{ $shipment->port_of_loading ?? '—' }}
                {{ $shipment->departure_date ? '· '.$shipment->departure_date->format('d M Y') : '' }}
            </div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Port of discharge / arrival</div>
            <div class="font-medium">
                {{ $shipment->port_of_discharge ?? '—' }}
                @if ($shipment->actual_arrival_at)
                    · {{ $shipment->actual_arrival_at->format('d M Y H:i') }} (actual)
                @elseif ($shipment->eta_at)
                    · {{ $shipment->eta_at->format('d M Y H:i') }} (estimate)
                @endif
            </div>
        </div>
    </div>
</div>
