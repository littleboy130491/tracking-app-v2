{{-- File: resources/views/livewire/customer/export-shipment-detail.blade.php
     Responsibility: Customer view of one export shipment, its journey and containers.
     What it does: shows shipment facts, the journey timeline and the sailing + containers card.
     How to use: rendered by App\Livewire\Customer\ExportShipmentDetail.
     How to extend: add customer-visible fields as they are published. --}}
<div>
    <a href="{{ route('customer.dashboard') }}" class="text-sm text-slate-500 transition hover:text-brand-600">
        &larr; Back to shipments
    </a>

    <h1 class="mt-2 text-2xl font-semibold text-slate-900">
        {{ $shipment->bl_number ?: 'Shipment' }}
    </h1>

    @include('livewire.customer.partials.shipment-summary', ['shipment' => $shipment, 'typeLabel' => 'Export', 'sailing' => $sailing])

    @include('components.shipment-timeline', ['entries' => $timeline])

    @if ($sailing !== [] || $containers->isNotEmpty())
        {{-- The sailing strip and the containers table share one card; the
             divider only appears between them when both are present. --}}
        <div class="mt-6 divide-y divide-slate-200 overflow-hidden rounded-xl bg-white shadow-sm">
            @include('livewire.customer.partials.sailing-information', ['sailing' => $sailing])

            @include('livewire.customer.partials.shipment-containers', [
                'containers' => $containers,
                'containerProgress' => $containerProgress,
            ])
        </div>
    @endif
</div>
