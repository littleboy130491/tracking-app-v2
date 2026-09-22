{{-- File: resources/views/livewire/customer/import-container-detail.blade.php
     Responsibility: Customer view of one import container.
     What it does: shipment back-link, import container facts and the journey timeline.
     How to use: rendered by App\Livewire\Customer\ImportContainerDetail.
     How to extend: entry display lives in the components.shipment-timeline view. --}}
<div>
    <a
        href="{{ route('customer.import-shipments.show', ['importShipment' => $container->import_shipment_id]) }}"
        class="text-sm text-slate-500 transition hover:text-brand-600"
    >
        &larr; Back to shipment{{ $container->shipment->bl_number ? ' '.$container->shipment->bl_number : '' }}
    </a>

    <h1 class="mt-2 text-2xl font-semibold text-slate-900">{{ $container->container_number }}</h1>

    <div class="mt-6 grid gap-4 rounded-xl bg-white p-6 shadow-sm md:grid-cols-3">
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Size</div>
            <div class="font-medium">{{ $container->size ?: '—' }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Driver</div>
            <div class="font-medium">{{ $container->driver_name ?: '—' }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Status</div>
            <div class="font-medium">{{ $container->status->label() }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Gate out CY</div>
            <div class="font-medium">{{ $container->gate_out_cy_at?->format('d M Y H:i') ?? '—' }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Gross weight (kg)</div>
            <div class="font-medium">
                {{ $container->gross_weight !== null ? rtrim(rtrim(number_format((float) $container->gross_weight, 3, '.', ','), '0'), '.') : '—' }}
            </div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">CBM</div>
            <div class="font-medium">{{ $container->cbm !== null ? rtrim(rtrim(number_format((float) $container->cbm, 3, '.', ','), '0'), '.') : '—' }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Return depot</div>
            <div class="font-medium">{{ $container->return_depot_name ?: '—' }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Empty returned</div>
            <div class="font-medium">{{ $container->empty_returned_at?->format('d M Y H:i') ?? '—' }}</div>
        </div>
    </div>

    @include('livewire.customer.partials.sailing-information', ['shipment' => $container->shipment])

    @include('components.shipment-timeline', ['entries' => $timeline])
</div>
