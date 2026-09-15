{{-- File: resources/views/livewire/customer/bill-of-lading-detail.blade.php
     Responsibility: Customer view of one shipment and its containers.
     What it does: shows shipment facts, the container list (links open a new
       tab) and the import draft-PIB confirmation actions.
     How to use: rendered by App\Livewire\Customer\BillOfLadingDetail.
     How to extend: add customer-visible fields as they are published. --}}
<div>
    <a href="{{ route('customer.dashboard') }}" class="text-sm text-slate-500 transition hover:text-brand-600">
        &larr; Back to shipments
    </a>

    <h1 class="mt-2 text-2xl font-semibold text-slate-900">
        {{ $billOfLading->reference_number }}
        <span class="text-base font-normal text-slate-500">
            {{ $billOfLading->bl_number ? '· '.$billOfLading->bl_number : '' }}
        </span>
    </h1>

    @if ($message)
        <div class="mt-4 rounded-md bg-accent-100 p-3 text-sm text-accent-600">{{ $message }}</div>
    @endif

    @if ($errors->any())
        <div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="mt-6 grid gap-4 rounded-xl bg-white p-6 shadow-sm md:grid-cols-3">
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Type</div>
            <div class="font-medium">{{ $billOfLading->shipment_type->label() }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Status</div>
            <div class="font-medium">{{ $billOfLading->status->label() }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Company</div>
            <div class="font-medium">{{ $billOfLading->company_name_snapshot }}</div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Vessel / voyage</div>
            <div class="font-medium">
                {{ $billOfLading->vessel_name ?: '—' }}
                {{ $billOfLading->voyage_number ? '/ '.$billOfLading->voyage_number : '' }}
            </div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">Loading → discharge</div>
            <div class="font-medium">
                {{ $billOfLading->port_of_loading ?: '—' }} &rarr; {{ $billOfLading->port_of_discharge ?: '—' }}
            </div>
        </div>
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-500">ETA</div>
            <div class="font-medium">{{ $billOfLading->eta_at?->format('d M Y H:i') ?? '—' }}</div>
        </div>
        <div class="md:col-span-3">
            <div class="text-xs uppercase tracking-wide text-slate-500">Description of goods</div>
            <div class="font-medium">{{ $billOfLading->goods_description ?: '—' }}</div>
        </div>
    </div>

    @if ($billOfLading->isImport())
        <div class="mt-6 rounded-xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Draft PIB confirmation</h2>
            <p class="mt-1 text-sm text-slate-500">
                Current status: <strong>{{ $billOfLading->draft_pib_confirmation_status->label() }}</strong>
                @if ($billOfLading->draft_pib_confirmed_at)
                    ({{ $billOfLading->draft_pib_confirmed_at->format('d M Y H:i') }})
                @endif
            </p>

            @if ($billOfLading->draft_pib_confirmation_notes)
                <p class="mt-2 text-sm text-slate-600">Your notes: {{ $billOfLading->draft_pib_confirmation_notes }}</p>
            @endif

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
    @endif

    <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 py-3">
            <h2 class="font-semibold text-slate-900">Containers</h2>
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Container</th>
                    <th class="px-4 py-3">Size / type</th>
                    <th class="px-4 py-3">Seal</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Empty returned</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($containers as $container)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a
                                href="{{ route('customer.containers.show', ['container' => $container->getKey()]) }}"
                                target="_blank"
                                rel="noopener"
                                class="font-medium text-brand-600 underline-offset-2 hover:text-brand-700 hover:underline"
                            >
                                {{ $container->container_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3">{{ trim(($container->size ?? '').' '.($container->type ?? '')) ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $container->seal_number ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $container->status->label() }}</td>
                        <td class="px-4 py-3">{{ $container->empty_returned_at?->format('d M Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                            No containers have been added yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
