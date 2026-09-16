{{-- File: resources/views/livewire/customer/container-detail.blade.php
     Responsibility: Customer view of one container.
     What it does: sailing header, container facts and the journey timeline.
     How to use: rendered by App\Livewire\Customer\ContainerDetail.
     How to extend: entry display lives in the components.shipment-timeline view. --}}
@php
    $billOfLading = $container->billOfLading;
@endphp
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

    <div class="mt-6 rounded-xl bg-white p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900">Sailing information</h2>
        <div class="mt-3 grid gap-4 text-sm md:grid-cols-2">
            <div>
                <div class="text-xs uppercase tracking-wide text-slate-500">Vessel</div>
                <div class="font-medium">
                    {{ trim(($billOfLading->vessel_name ?? '').' '.($billOfLading->voyage_number ?? '')) ?: '—' }}
                </div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-wide text-slate-500">Shipping line</div>
                <div class="font-medium">{{ $billOfLading->shipping_line ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-wide text-slate-500">Port of loading / departure</div>
                <div class="font-medium">
                    {{ $billOfLading->port_of_loading ?? '—' }}
                    {{ $billOfLading->departure_date ? '· '.$billOfLading->departure_date->format('d M Y') : '' }}
                </div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-wide text-slate-500">Port of discharge / arrival</div>
                <div class="font-medium">
                    {{ $billOfLading->port_of_discharge ?? '—' }}
                    @if ($billOfLading->actual_arrival_at)
                        · {{ $billOfLading->actual_arrival_at->format('d M Y H:i') }} (actual)
                    @elseif ($billOfLading->eta_at)
                        · {{ $billOfLading->eta_at->format('d M Y H:i') }} (estimate)
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('components.shipment-timeline', ['entries' => $timeline])
</div>
