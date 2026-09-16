{{-- File: resources/views/livewire/customer/container-detail.blade.php
     Responsibility: Customer view of one container.
     What it does: shows container facts plus the latest tracking position.
     How to use: rendered by App\Livewire\Customer\ContainerDetail.
     How to extend: add a progress feed from customer-visible activity logs. --}}
<div>
    <a
        href="{{ route('customer.bill-of-ladings.show', ['billOfLading' => $container->bill_of_lading_id]) }}"
        class="text-sm text-slate-500 transition hover:text-brand-600"
    >
        &larr; Back to shipment {{ $container->billOfLading->reference_number }}
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
            <div class="text-xs uppercase tracking-wide text-slate-500">Return depot</div>
            <div class="font-medium">{{ $container->return_depot_name ?: '—' }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Empty returned</div>
            <div class="font-medium">{{ $container->empty_returned_at?->format('d M Y H:i') ?? '—' }}</div>
        </div>
    </div>
</div>
