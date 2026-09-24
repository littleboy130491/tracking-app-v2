{{-- File: resources/views/components/shipment-timeline.blade.php
     Responsibility: Renders one customer tracking-progress list.
     What it does: shows shipment milestones as a vertical line with numbered
       nodes (done = accent check, current = brand circle + card, upcoming =
       grey); milestone facts sit on the left, populated field values on
       the right on wider screens.
     How to use: @include('components.shipment-timeline', ['entries' => $timeline]).
     How to extend: entry shape is App\Services\ShipmentTimelineEntry. --}}
<div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm">
    <div class="border-b border-slate-200 px-4 py-3 sm:px-6">
        <h2 class="flex items-center gap-2 font-semibold text-slate-900">Tracking progress</h2>
    </div>
    @if (count($entries) === 0)
        <p class="px-4 py-8 text-center text-sm text-slate-500 sm:px-6">
            No tracking progress has been recorded for this shipment yet.
        </p>
    @else
        <div class="px-4 py-4 sm:px-6">
        <ol aria-label="Tracking progress">
            @foreach ($entries as $entry)
                <li @class(['relative flex gap-3', 'pb-5' => ! $loop->last]) @if ($entry->isLatest) aria-current="step" @endif>
                    @if (! $loop->last)
                        <span
                            aria-hidden="true"
                            class="absolute bottom-0 left-3.5 top-7 w-0.5 -translate-x-1/2 rounded-full {{ $entry->isPending || $entry->isLatest ? 'bg-slate-200' : 'bg-accent-500' }}"
                        ></span>
                    @endif
                    <span class="relative flex h-7 w-7 shrink-0 items-center justify-center">
                        @if ($entry->isLatest)
                            <span class="relative flex h-7 w-7 items-center justify-center rounded-full bg-accent-500 text-xs font-semibold text-white">{{ $loop->iteration }}</span>
                        @elseif ($entry->isPending)
                            <span class="flex h-7 w-7 items-center justify-center rounded-full border border-slate-300 bg-white text-xs font-medium text-slate-400">{{ $loop->iteration }}</span>
                        @else
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-accent-500">
                                <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4l3.3 3.29 7.3-7.29a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        @endif
                    </span>
                    <div @class(['min-w-0 flex-1', 'grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.25fr)] md:gap-6' => $entry->fields !== []])>
                        <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-medium {{ $entry->isPending ? 'text-slate-400' : 'text-slate-900' }}">
                                {{ $entry->title }}
                            </p>
                            @if ($entry->isLatest)
                                <span class="rounded bg-accent-100 px-1.5 py-0.5 text-xs font-medium text-accent-600">Latest</span>
                            @endif
                            <span class="sr-only">{{ $entry->isPending ? 'Upcoming step' : ($entry->isLatest ? 'Current step' : 'Completed step') }}</span>
                        </div>
                        @if (! $entry->isPending && $entry->occurredAt)
                            <p class="mt-0.5 text-xs text-slate-500">{{ $entry->occurredAt }}</p>
                        @endif
                        </div>
                        @if ($entry->fields !== [])
                            <dl class="grid min-w-0 content-start gap-2 border-t border-slate-200 pt-2 md:border-t-0 md:pt-0">
                                @foreach ($entry->fields as $field)
                                    <div class="min-w-0">
                                        <dt class="text-xs font-medium text-slate-500">{{ $field['label'] }}</dt>
                                        <dd class="mt-0.5 break-words text-sm text-slate-700">{{ $field['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
        </div>
    @endif
</div>
