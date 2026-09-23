{{-- File: resources/views/livewire/customer/partials/shipment-summary.blade.php
     Responsibility: The customer-visible shipment facts grid.
     What it does: shows company, status pill, type, shipment mode, AJU
       number, container count, document received date and completion date
       for one shipment; the goods description row is import-only.
     How to use: @include('livewire.customer.partials.shipment-summary', ['shipment' => $shipment, 'typeLabel' => 'Export']).
     How to extend: add customer-visible fields here once they are published. --}}
<div class="mt-6 grid gap-4 rounded-xl bg-white p-6 shadow-sm md:grid-cols-3">
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Company</div>
        <div class="font-medium">{{ $shipment->company_name_snapshot }}</div>
    </div>
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Status</div>
        <div>
            @php($statusColor = match ($shipment->status) {
                \App\Enums\ShipmentStatus::Completed => 'bg-accent-100 text-accent-600',
                \App\Enums\ShipmentStatus::Cancelled => 'bg-red-50 text-red-700',
                default => 'bg-amber-50 text-amber-700',
            })
            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColor }}">
                {{ $shipment->status->label() }}
            </span>
        </div>
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
        <div class="font-medium">{{ $shipment->containers->count() ?: '—' }}</div>
    </div>
    <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Document received date</div>
        <div class="font-medium">{{ $shipment->document_received_date?->format('d M Y') ?? '—' }}</div>
    </div>
    @if ($shipment->completed_at)
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Completed at</div>
            <div class="font-medium">{{ $shipment->completed_at->format('d M Y') }}</div>
        </div>
    @endif
    @if ($typeLabel === 'Import')
        <div class="md:col-span-3">
            <div class="text-xs uppercase tracking-wide text-slate-500">Description of goods</div>
            <div class="font-medium">{{ $shipment->goods_description ?: '—' }}</div>
        </div>
    @endif
</div>
