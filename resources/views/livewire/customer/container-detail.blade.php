{{-- File: resources/views/livewire/customer/container-detail.blade.php
     Responsibility: Customer view of one container.
     What it does: shows container facts plus only the customer-visible
       location updates.
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

    <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 py-3">
            <h2 class="font-semibold text-slate-900">Location history</h2>
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Location</th>
                    <th class="px-4 py-3">Reported</th>
                    <th class="px-4 py-3">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($locations as $location)
                    <tr>
                        <td class="px-4 py-3">{{ $location->location_name }}</td>
                        <td class="px-4 py-3">{{ $location->reported_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $location->notes ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                            No location updates shared yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
