{{-- File: resources/views/livewire/customer/dashboard.blade.php
     Responsibility: Customer portal home: greeting, filters and shipment list.
     What it does: binds the filter inputs (company, number, status, year, month)
       to the Dashboard component state and renders the paginated bills of lading
       with their company and a link to the detail page.
     How to use: rendered by App\Livewire\Customer\Dashboard.
     How to extend: add columns once more customer-visible fields exist. --}}
<div>
    <h1 class="text-2xl font-semibold text-slate-900">
        Hello, <span class="text-brand-600">{{ auth()->user()->name }}</span>
    </h1>
    <p class="mt-1 text-sm text-slate-500">
        Here are the shipments of the companies you manage.
    </p>

    <div class="mt-6 grid gap-3 rounded-xl bg-white p-4 shadow-sm md:grid-cols-3 lg:grid-cols-6">
        <div class="md:col-span-2 lg:col-span-2">
            <label for="number" class="block text-xs font-medium text-slate-500">B/L or reference number</label>
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

        <div class="flex items-end md:col-span-3 lg:col-span-6">
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
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Containers</th>
                    <th class="px-4 py-3">ETA</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($billOfLadings as $billOfLading)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a
                                href="{{ route('customer.bill-of-ladings.show', ['billOfLading' => $billOfLading->getKey()]) }}"
                                class="font-medium text-brand-600 underline-offset-2 hover:text-brand-700 hover:underline"
                            >
                                {{ $billOfLading->reference_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3">{{ $billOfLading->bl_number ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $billOfLading->company->name }}</td>
                        <td class="px-4 py-3">{{ $billOfLading->shipment_type->label() }}</td>
                        <td class="px-4 py-3">{{ $billOfLading->status->label() }}</td>
                        <td class="px-4 py-3">{{ $billOfLading->containers->count() }}</td>
                        <td class="px-4 py-3">{{ $billOfLading->eta_at?->format('d M Y H:i') ?? '—' }}</td>
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

    <div class="mt-4">
        {{ $billOfLadings->links() }}
    </div>
</div>
