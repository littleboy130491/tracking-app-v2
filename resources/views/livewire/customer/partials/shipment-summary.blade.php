{{-- File: resources/views/livewire/customer/partials/shipment-summary.blade.php
     Responsibility: The customer-visible shipment facts grid.
     What it does: shows company, status pill, type, shipment mode, AJU
       number, container count, document received date and completion date
       for one shipment; the goods description row is import-only.
     How to use: @include('livewire.customer.partials.shipment-summary', ['shipment' => $shipment, 'typeLabel' => 'Export']).
     How to extend: add customer-visible fields here once they are published. --}}
<div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm">
    <div class="border-b border-slate-200 px-4 py-3 sm:px-6">
        <h2 class="flex items-center gap-2 font-semibold text-slate-900">Shipment overview</h2>
    </div>
    <div class="grid gap-4 px-4 py-4 sm:px-6 md:grid-cols-3">
    <div>
        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Company</div>
        <div class="font-medium text-slate-900">{{ $shipment->company_name_snapshot }}</div>
    </div>
    <div>
        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Status</div>
        <div>
            @php($statusColor = match ($shipment->status) {
                \App\Enums\ShipmentStatus::Completed => 'bg-accent-100 text-accent-600',
                \App\Enums\ShipmentStatus::Cancelled => 'bg-red-100 text-red-700',
                default => 'bg-amber-100 text-amber-700',
            })
            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColor }}">
                {{ $shipment->status->label() }}
            </span>
        </div>
    </div>
    <div>
        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Type</div>
        <div class="font-medium text-slate-900">{{ $typeLabel }}</div>
    </div>
    <div>
        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Shipment mode</div>
        <div class="font-medium text-slate-900">{{ $shipment->shipment_mode?->label() ?? '—' }}</div>
    </div>
    <div>
        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">AJU number</div>
        <div class="font-medium text-slate-900">{{ $shipment->aju_number ?: '—' }}</div>
    </div>
    <div>
        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Containers</div>
        <div class="font-medium text-slate-900">{{ $shipment->containers->count() ?: '—' }}</div>
    </div>
    <div>
        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Document received date</div>
        <div class="font-medium text-slate-900">{{ $shipment->document_received_date?->format('d M Y') ?? '—' }}</div>
    </div>
    @if ($shipment->completed_at)
        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Completed at</div>
            <div class="font-medium text-slate-900">{{ $shipment->completed_at->format('d M Y') }}</div>
        </div>
    @endif
    @if ($typeLabel === 'Import')
        <div class="md:col-span-3">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Description of goods</div>
            <div class="font-medium text-slate-900">{{ $shipment->goods_description ?: '—' }}</div>
        </div>
    @endif
    </div>
</div>
