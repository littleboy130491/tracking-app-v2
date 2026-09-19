{{-- File: resources/views/livewire/customer/dashboard.blade.php
     Responsibility: Customer portal home: greeting, Type filter, filters and shipment list.
     What it does: binds the filter inputs (type, company, number, status, year,
       month) to the Dashboard component state and renders the paginated shipments
       of the selected type with their company, latest journey place/event and a link.
     How to use: rendered by App\Livewire\Customer\Dashboard.
     How to extend: add columns once more customer-visible fields exist. --}}
<div>
    <h1 class="text-2xl font-semibold text-slate-900">
        Hello, <span class="text-brand-600">{{ auth()->user()->name }}</span>
    </h1>

    <div class="mt-6 grid gap-3 rounded-xl bg-white p-4 shadow-sm md:grid-cols-3 lg:grid-cols-7">
        <div>
            <label for="type" class="block text-xs font-medium text-slate-500">Type</label>
            <select
                id="type"
                wire:model.live="type"
                class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
            >
                <option value="export">Export</option>
                <option value="import">Import</option>
            </select>
        </div>

        <div class="md:col-span-2 lg:col-span-2">
            <label for="number" class="block text-xs font-medium text-slate-500">B/L, reference, container or seal</label>
            <input
                id="number"
                type="text"
                wire:model.live.debounce.400ms="number"
                placeholder="Search number…"
                class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
            >
        </div>

        <div>
            <label for="company" class="block text-xs font-medium text-slate-500">Company</label>
            <select
                id="company"
                wire:model.live="company"
                class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
            >
                <option value="">All companies</option>
                @foreach ($companies as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status" class="block text-xs font-medium text-slate-500">Status</label>
            <select
                id="status"
                wire:model.live="status"
                class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
            >
                <option value="">All</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="year" class="block text-xs font-medium text-slate-500">Year</label>
            <select
                id="year"
                wire:model.live="year"
                class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
            >
                <option value="">All</option>
                @foreach ($years as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="month" class="block text-xs font-medium text-slate-500">Month</label>
            <select
                id="month"
                wire:model.live="month"
                class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
            >
                <option value="">All</option>
                @foreach ($months as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end md:col-span-3 lg:col-span-7">
            <button
                type="button"
                wire:click="clearFilters"
                class="rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700"
            >
                Clear filters
            </button>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">B/L number</th>
                    <th class="px-4 py-3">Company</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Containers</th>
                    <th class="px-4 py-3">Latest place</th>
                    <th class="px-4 py-3">Latest event</th>
                    <th class="px-4 py-3">ETA</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($shipments as $shipment)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a
                                href="{{ route($routeName, [$routeParam => $shipment->getKey()]) }}"
                                class="font-medium text-brand-600 underline-offset-2 hover:text-brand-700 hover:underline"
                            >
                                {{ $shipment->reference_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3">{{ $shipment->bl_number ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $shipment->company->name }}</td>
                        <td class="px-4 py-3">{{ $shipment->status->label() }}</td>
                        <td class="px-4 py-3">{{ $shipment->containers->count() }}</td>
                        @php($entry = $latest[$shipment->getKey()] ?? null)
                        <td class="px-4 py-3">{{ $entry?->location ?? '—' }}</td>
                        <td class="px-4 py-3">
                            {{ $entry?->title ?? '—' }}
                            @if ($entry)
                                <span class="block text-xs text-slate-500">{{ $entry->occurredAt }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $shipment->eta_at?->format('d M Y H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                            No shipments match your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $shipments->links() }}
    </div>
</div>
