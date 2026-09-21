{{-- File: resources/views/livewire/customer/import-shipment-detail.blade.php
     Responsibility: Customer view of one import shipment, its journey, containers and draft PIB.
     What it does: shows shipment facts, the draft-PIB confirmation actions, the
       journey timeline and the container list.
     How to use: rendered by App\Livewire\Customer\ImportShipmentDetail.
     How to extend: add customer-visible fields as they are published. --}}
<div>
    <a href="{{ route('customer.dashboard') }}" class="text-sm text-slate-500 transition hover:text-brand-600">
        &larr; Back to shipments
    </a>

    <h1 class="mt-2 text-2xl font-semibold text-slate-900">
        {{ $shipment->bl_number ?: 'Shipment' }}
    </h1>

    @if ($message)
        <div class="mt-4 rounded-md bg-accent-100 p-3 text-sm text-accent-600">{{ $message }}</div>
    @endif

    @if ($errors->any())
        <div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    @include('livewire.customer.partials.shipment-summary', ['shipment' => $shipment, 'typeLabel' => 'Import'])

    <div class="mt-6 rounded-xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Draft PIB confirmation</h2>
        <p class="mt-1 text-sm text-slate-500">
            Current status: <strong>{{ $draftPibConfirmed ? 'Confirmed' : 'Waiting for your confirmation' }}</strong>
        </p>

        @if ($draftPibConfirmed)
            <p class="mt-4 rounded-md bg-accent-100 p-3 text-sm text-accent-600">
                This draft PIB is confirmed — no action is needed. Please contact us if something still has to change.
            </p>
        @else
            <div class="mt-4 flex flex-wrap items-start gap-3">
                <button
                    type="button"
                    wire:click="confirm"
                    class="rounded-md bg-accent-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-accent-100"
                >
                    Confirm draft PIB
                </button>
            </div>

            <div class="mt-4">
                <label for="revisionNotes" class="block text-sm font-medium text-slate-700">
                    Need a change? Tell us what to revise
                </label>
                <textarea
                    id="revisionNotes"
                    wire:model="revisionNotes"
                    rows="3"
                    class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                ></textarea>
                <button
                    type="button"
                    wire:click="requestRevision"
                    class="mt-2 rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700"
                >
                    Request revision
                </button>
            </div>
        @endif
    </div>

    @include('components.shipment-timeline', ['entries' => $timeline])

    @include('livewire.customer.partials.shipment-containers', [
        'containers' => $containers,
        'routeName' => 'customer.import-containers.show',
        'routeParam' => 'importContainer',
    ])
</div>
