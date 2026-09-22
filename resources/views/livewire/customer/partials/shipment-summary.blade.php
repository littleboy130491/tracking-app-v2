{{-- File: resources/views/livewire/customer/partials/shipment-summary.blade.php
     Responsibility: The customer-visible shipment facts grid.
     What it does: shows company, type, shipment mode, AJU number, container
       count and document-created date for one shipment; the goods description
       row is import-only.
     How to use: @include('livewire.customer.partials.shipment-summary', ['shipment' => $shipment, 'typeLabel' => 'Export']).
     How to extend: add customer-visible fields here once they are published. --}}
<div class="mt-6 grid gap-4 rounded-xl bg-white p-6 shadow-sm md:grid-cols-3">
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Company</div>
        <div class="font-medium">{{ $shipment->company_name_snapshot }}</div>
    </div>
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Type</div>
        <div class="font-medium">{{ $typeLabel }}</div>
    </div>
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Shipment mode</div>
        <div class="font-medium">{{ $shipment->shipment_mode?->label() ?? '—' }}</div>
    </div>
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">AJU number</div>
        <div class="font-medium">{{ $shipment->aju_number ?: '—' }}</div>
    </div>
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Containers</div>
        <div class="font-medium">{{ $shipment->containers->count() }}</div>
    </div>
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Document created</div>
        <div class="font-medium">{{ $shipment->document_received_date?->format('d M Y') ?? '—' }}</div>
    </div>
    @if ($typeLabel === 'Import')
        <div class="md:col-span-3">
            <div class="text-xs uppercase tracking-wide text-slate-500">Description of goods</div>
            <div class="font-medium">{{ $shipment->goods_description ?: '—' }}</div>
        </div>
    @endif
</div>
