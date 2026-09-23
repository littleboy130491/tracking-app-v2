{{-- File: resources/views/components/shipment-timeline.blade.php
     Responsibility: Renders one customer tracking-progress list.
     What it does: shows milestone details on the left and populated field
       values on the right on wider screens; the latest row is highlighted.
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
                    <div class="grid min-w-0 flex-1 gap-3 {{ $entry->fields !== [] ? 'md:grid-cols-[minmax(0,1fr)_minmax(0,1.25fr)] md:gap-6' : '' }}">
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
                                @endif
                            </div>
                        </div>
                        @if ($entry->fields !== [])
                            <dl class="grid min-w-0 content-start gap-1 border-t border-slate-200 pt-2 text-sm md:border-t-0 md:pl-4 md:pt-0">
                                @foreach ($entry->fields as $field)
                                    <div class="grid gap-0.5 sm:grid-cols-[9rem_minmax(0,1fr)] sm:gap-2">
                                        <dt class="text-xs font-medium text-slate-500">{{ $field['label'] }}</dt>
                                        <dd class="whitespace-pre-line break-words text-slate-700">{{ $field['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</div>
