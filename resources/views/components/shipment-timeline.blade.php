{{-- File: resources/views/components/shipment-timeline.blade.php
     Responsibility: Renders one customer tracking-progress list.
     What it does: oldest-first rows with time, place and detail drawn from the
       shipment's voyage dates and its customer-visible activity log (milestones
       included); the latest row is highlighted.
     How to use: @include('components.shipment-timeline', ['entries' => $timeline]).
     How to extend: entry shape is App\Services\ShipmentTimelineEntry. --}}
<div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm">
    <div class="border-b border-slate-200 px-4 py-3">
        <h2 class="font-semibold text-slate-900">Tracking progress</h2>
    </div>
    @if (count($entries) === 0)
        <p class="px-4 py-8 text-center text-sm text-slate-500">
            No tracking progress has been recorded for this shipment yet.
        </p>
    @else
        <ol class="divide-y divide-slate-100">
            @foreach ($entries as $entry)
                <li class="flex gap-3 px-4 py-3 {{ $entry->isLatest ? 'bg-accent-100/40' : '' }}">
                    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $entry->isPending ? 'bg-slate-200' : 'bg-accent-500' }}"></span>
                    <div class="min-w-0 text-sm">
                        <div class="font-medium {{ $entry->isPending ? 'text-slate-400' : 'text-slate-900' }}">
                            {{ $entry->title }}
                            @if ($entry->isLatest)
                                <span class="ml-1 rounded bg-accent-100 px-1.5 py-0.5 text-xs font-normal text-accent-600">latest</span>
                            @endif
                        </div>
                        <div class="mt-0.5 text-slate-500">
                            @if ($entry->isPending)
                                Pending
                            @else
                                {{ $entry->occurredAt ?? '—' }}
                                @if ($entry->location)
                                    · {{ $entry->location }}
                                @endif
                            @endif
                        </div>
                        @if ($entry->detail)
                            <div class="mt-0.5 text-slate-600">{{ $entry->detail }}</div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</div>
