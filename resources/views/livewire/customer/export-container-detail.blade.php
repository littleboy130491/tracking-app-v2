{{-- File: resources/views/livewire/customer/export-container-detail.blade.php
     Responsibility: Customer view of one export container.
     What it does: shipment back-link, export container facts and the journey timeline.
     How to use: rendered by App\Livewire\Customer\ExportContainerDetail.
     How to extend: entry display lives in the components.shipment-timeline view. --}}
<div>
    <a
        href="{{ route('customer.export-shipments.show', ['exportShipment' => $container->export_shipment_id]) }}"
        class="text-sm text-slate-500 transition hover:text-brand-600"
    >
        &larr; Back to shipment{{ $container->shipment->bl_number ? ' '.$container->shipment->bl_number : '' }}
    </a>

    <h1 class="mt-2 text-2xl font-semibold text-slate-900">{{ $container->container_number }}</h1>

    <div class="mt-6 grid gap-4 rounded-xl bg-white p-6 shadow-sm md:grid-cols-3">
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Size / type</div>
            <div class="font-medium">{{ trim(($container->size ?? '').' '.($container->type ?? '')) ?: '—' }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Seal number</div>
            <div class="font-medium">{{ $container->seal_number ?: '—' }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Status</div>
            <div class="font-medium">{{ $container->status->label() }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Tracking position</div>
            <div class="font-medium">{{ $container->tracking_position ?: '—' }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Gate in CY</div>
            <div class="font-medium">{{ $container->gate_in_cy_at?->format('d M Y H:i') ?? '—' }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">VGM (kg)</div>
            <div class="font-medium">{{ $container->vgm_value !== null ? rtrim(rtrim(number_format((float) $container->vgm_value, 3, '.', ','), '0'), '.') : '—' }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Final checked</div>
            <div class="font-medium">
                {{ $container->final_checked ? 'Yes' : 'No' }}
                {{ $container->final_checked_at ? '· '.$container->final_checked_at->format('d M Y H:i') : '' }}
            </div>
        </div>
    </div>

    @include('livewire.customer.partials.sailing-information', ['shipment' => $container->shipment])

    @include('components.shipment-timeline', ['entries' => $timeline])
</div>
