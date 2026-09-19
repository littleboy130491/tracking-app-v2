{{-- File: resources/views/livewire/customer/export-shipment-detail.blade.php
     Responsibility: Customer view of one export shipment, its journey and containers.
     What it does: shows shipment facts, the journey timeline and the container list.
     How to use: rendered by App\Livewire\Customer\ExportShipmentDetail.
     How to extend: add customer-visible fields as they are published. --}}
<div>
    <a href="{{ route('customer.dashboard') }}" class="text-sm text-slate-500 transition hover:text-brand-600">
        &larr; Back to shipments
    </a>

    <h1 class="mt-2 text-2xl font-semibold text-slate-900">
        {{ $shipment->bl_number ?: 'Shipment' }}
    </h1>

    @include('livewire.customer.partials.shipment-summary', ['shipment' => $shipment, 'typeLabel' => 'Export'])

    @include('components.shipment-timeline', ['entries' => $timeline])

    @include('livewire.customer.partials.shipment-containers', [
        'containers' => $containers,
        'routeName' => 'customer.export-containers.show',
        'routeParam' => 'exportContainer',
    ])
</div>
