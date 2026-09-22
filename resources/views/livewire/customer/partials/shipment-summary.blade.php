{{-- File: resources/views/livewire/customer/partials/shipment-summary.blade.php
     Responsibility: The customer-visible shipment facts grid.
     What it does: shows type, status, company, vessel/voyage, ports and ETA for one
       shipment; the goods description row is import-only.
     How to use: @include('livewire.customer.partials.shipment-summary', ['shipment' => $shipment, 'typeLabel' => 'Export']).
     How to extend: add customer-visible fields here once they are published. --}}
<div class="mt-6 grid gap-4 rounded-xl bg-white p-6 shadow-sm md:grid-cols-3">
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Type</div>
        <div class="font-medium">{{ $typeLabel }}</div>
    </div>
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Status</div>
        <div class="font-medium">{{ $shipment->status->label() }}</div>
    </div>
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Company</div>
        <div class="font-medium">{{ $shipment->company_name_snapshot }}</div>
    </div>
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Vessel / voyage</div>
        <div class="font-medium">
            {{ $shipment->vessel_name ?: '—' }}
            {{ $shipment->voyage_number ? '/ '.$shipment->voyage_number : '' }}
        </div>
    </div>
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Loading → discharge</div>
        <div class="font-medium">
            {{ $shipment->port_of_loading ?: '—' }} &rarr; {{ $shipment->port_of_discharge ?: '—' }}
        </div>
    </div>
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">ETA</div>
        <div class="font-medium">{{ $shipment->eta_at?->format('d M Y H:i') ?? '—' }}</div>
    </div>
    @if ($typeLabel === 'Import')
        <div class="md:col-span-3">
            <div class="text-xs uppercase tracking-wide text-slate-500">Description of goods</div>
            <div class="font-medium">{{ $shipment->goods_description ?: '—' }}</div>
        </div>
    @endif
</div>
