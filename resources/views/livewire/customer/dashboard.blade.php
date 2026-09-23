{{-- File: resources/views/livewire/customer/dashboard.blade.php
     Responsibility: Customer portal home: greeting, filter bar and shipment list.
     What it does: binds the filter inputs (type, company, number, status, year,
       month) to the Dashboard component state and renders the combined
       paginated shipments (export + import) with their type badge, company,
       status, POD / vessel arrival, latest reached milestone and a detail link.
     How to use: rendered by App\Livewire\Customer\Dashboard.
     How to extend: add columns once more customer-visible fields exist. --}}
<div>
    <h1 class="text-2xl font-semibold text-slate-900">
        Hello, <span class="text-brand-600">{{ auth()->user()->name }}</span>
    </h1>

    <div class="mt-6 rounded-xl bg-white p-4 shadow-sm">
        {{-- Primary search spans the full width; secondary filters sit in a
             compact row below it so the bar stays tidy on every breakpoint. --}}
        <div class="relative">
            <label for="number" class="mb-1 block text-xs font-medium text-slate-500">
                Search B/L number, container or seal
            </label>
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    id="number"
                    type="text"
                    wire:model.live.debounce.400ms="number"
                    placeholder="Search by B/L, container or seal…"
                    class="w-full rounded-md border border-slate-300 py-2 pl-9 pr-3 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                >
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5">
            <div>
                <label for="type" class="mb-1 block text-xs font-medium text-slate-500">Type</label>
                <select
                    id="type"
                    wire:model.live="type"
                    class="w-full rounded-md border border-slate-300 py-2 pl-3 pr-8 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                >
                    <option value="">All</option>
                    <option value="export">Export</option>
                    <option value="import">Import</option>
                </select>
            </div>

            <div>
                <label for="company" class="mb-1 block text-xs font-medium text-slate-500">Company</label>
                <select
                    id="company"
                    wire:model.live="company"
                    class="w-full rounded-md border border-slate-300 py-2 pl-3 pr-8 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                >
                    <option value="">All companies</option>
                    @foreach ($companies as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="mb-1 block text-xs font-medium text-slate-500">Status</label>
                <select
                    id="status"
                    wire:model.live="status"
                    class="w-full rounded-md border border-slate-300 py-2 pl-3 pr-8 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                >
                    <option value="">All</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="year" class="mb-1 block text-xs font-medium text-slate-500">Year</label>
                <select
                    id="year"
                    wire:model.live="year"
                    class="w-full rounded-md border border-slate-300 py-2 pl-3 pr-8 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                >
                    <option value="">All</option>
                    @foreach ($years as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="month" class="mb-1 block text-xs font-medium text-slate-500">Month</label>
                <select
                    id="month"
                    wire:model.live="month"
                    class="w-full rounded-md border border-slate-300 py-2 pl-3 pr-8 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                >
                    <option value="">All</option>
                    @foreach ($months as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <p class="text-xs text-slate-500">
                {{ $shipments->total() }} {{ Str::plural('shipment', $shipments->total()) }} found
            </p>
            <button
                type="button"
                wire:click="clearFilters"
                class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700"
            >
                Clear filters
            </button>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">B/L number</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Company</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">POD / Vessel arrival</th>
                    <th class="px-4 py-3">Latest event</th>
                    <th class="px-4 py-3">Document received date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($shipments as $shipment)
                    @php($isExportRow = $shipment instanceof \App\Models\ExportShipment)
                    @php($detailUrl = $isExportRow
                        ? route('customer.export-shipments.show', ['exportShipment' => $shipment->getKey()])
                        : route('customer.import-shipments.show', ['importShipment' => $shipment->getKey()]))
                    <tr class="cursor-pointer hover:bg-slate-50" onclick="window.location='{{ $detailUrl }}'">
                        <td class="px-4 py-3">
                            <a
                                href="{{ $detailUrl }}"
                                class="font-medium text-brand-600 underline-offset-2 hover:text-brand-700 hover:underline"
                            >
                                {{ $shipment->bl_number ?: '—' }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $isExportRow ? 'bg-brand-50 text-brand-700' : 'bg-green-50 text-green-700' }}">
                                {{ $isExportRow ? 'Export' : 'Import' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $shipment->company->name }}</td>
                        <td class="px-4 py-3">
                            @php($statusColor = match ($shipment->status) {
                                \App\Enums\ShipmentStatus::Completed => 'bg-accent-100 text-accent-600',
                                \App\Enums\ShipmentStatus::Cancelled => 'bg-red-50 text-red-700',
                                default => 'bg-amber-50 text-amber-700',
                            })
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColor }}">
                                {{ $shipment->status->label() }}
                            </span>
                        </td>
                        @php($rowKey = $shipment::class.':'.$shipment->getKey())
                        @php($entry = $latest[$rowKey] ?? null)
                        @php($sail = $sailing[$rowKey] ?? [])
                        <td class="px-4 py-3">
                            @if (filled($sail['Port of discharge'] ?? null))
                                {{ $sail['Port of discharge'] }}
                                @if (filled($sail['Actual arrival'] ?? null))
                                    <span class="block text-xs text-slate-500">{{ $sail['Actual arrival'] }}</span>
                                @elseif (filled($sail['Arrival time / ETA'] ?? null))
                                    <span class="block text-xs text-slate-500">ETA {{ $sail['Arrival time / ETA'] }}</span>
                                @endif
                            @elseif (filled($sail['Actual arrival'] ?? null))
                                {{ $sail['Actual arrival'] }}
                            @elseif (filled($sail['Arrival time / ETA'] ?? null))
                                ETA {{ $sail['Arrival time / ETA'] }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            {{ $entry?->title ?? '—' }}
                            @if ($entry)
                                <span class="block text-xs text-slate-500">{{ $entry->occurredAt }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $shipment->document_received_date?->format('d M Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                            No shipments match your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-sm text-slate-600">
            <label for="perPage">Show</label>
            <select
                id="perPage"
                wire:model.live="perPage"
                class="rounded-md border border-slate-300 px-2 py-1.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
            >
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span>per page</span>
        </div>
        {{ $shipments->links() }}
    </div>
</div>
