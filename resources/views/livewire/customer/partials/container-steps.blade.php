{{-- File: resources/views/livewire/customer/partials/container-steps.blade.php
     Responsibility: One container's step-by-step journey inside the accordion.
     What it does: renders each container milestone of the B/L as done (accent
       check), current (brand circle with ping halo + card) or upcoming (grey);
       milestone facts sit on the left, populated field values on the right
       on wider screens; cancelled and not-started states get a small callout.
     How to use: @include('livewire.customer.partials.container-steps', ['progress' => $progress]).
     How to extend: step data is App\Services\ContainerProgress, built by
       ShipmentTimeline::forContainers(). --}}
<section class="rounded-lg bg-white p-4 ring-1 ring-slate-100">
    <h3 class="text-sm font-semibold text-slate-900">Cargo tracking</h3>

    @if ($progress->status === \App\Enums\ContainerStatus::Cancelled)
        <p class="mt-3 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">This container was cancelled.</p>
    @elseif ($progress->reachedCount() === 0 && $progress->steps !== [])
        <p class="mt-3 rounded-md bg-brand-50 px-3 py-2 text-sm text-brand-700">
            Your container's journey starts at {{ $progress->steps[0]->title }}.
        </p>
    @endif

    @if ($progress->steps !== [])
        <ol class="mt-4" aria-label="Cargo tracking">
            @foreach ($progress->steps as $step)
                <li @class(['relative flex gap-3', 'pb-5' => ! $loop->last]) @if ($step->isLatest) aria-current="step" @endif>
                    @if (! $loop->last)
                        <span
                            aria-hidden="true"
                            class="absolute bottom-0 left-3.5 top-7 w-0.5 -translate-x-1/2 rounded-full {{ $step->isPending || $step->isLatest ? 'bg-slate-200' : 'bg-accent-500' }}"
                        ></span>
                    @endif
                    <span class="relative flex h-7 w-7 shrink-0 items-center justify-center">
                        @if ($step->isLatest)
                            <span class="relative flex h-7 w-7 items-center justify-center rounded-full bg-accent-500 text-xs font-semibold text-white">{{ $loop->iteration }}</span>
                        @elseif ($step->isPending)
                            <span class="flex h-7 w-7 items-center justify-center rounded-full border border-slate-300 bg-white text-xs font-medium text-slate-400">{{ $loop->iteration }}</span>
                        @else
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-accent-500">
                                <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4l3.3 3.29 7.3-7.29a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        @endif
                    </span>
                    <div @class(['min-w-0 flex-1', 'grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.25fr)] md:gap-6' => $step->fields !== []])>
                        <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-medium {{ $step->isPending ? 'text-slate-400' : 'text-slate-900' }}">
                                {{ $step->title }}
                            </p>
                            @if ($step->isLatest)
                                <span class="rounded bg-accent-100 px-1.5 py-0.5 text-xs font-medium text-accent-600">Latest</span>
                            @endif
                            <span class="sr-only">{{ $step->isPending ? 'Upcoming step' : ($step->isLatest ? 'Current step' : 'Completed step') }}</span>
                        </div>
                        @if (! $step->isPending && $step->occurredAt)
                            <p class="mt-0.5 text-xs text-slate-500">{{ $step->occurredAt }}</p>
                        @endif
                        </div>
                        @if ($step->fields !== [])
                            <dl class="grid min-w-0 content-start gap-2 border-t border-slate-200 pt-2 md:border-t-0 md:pt-0">
                                @foreach ($step->fields as $field)
                                    <div class="min-w-0">
                                        <dt class="text-xs font-medium text-slate-500">{{ $field['label'] }}</dt>
                                        <dd class="mt-0.5 break-words text-sm text-slate-700">
                                            @if (isset($field['href']))
                                                <a
                                                    href="{{ $field['href'] }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="font-medium text-brand-700 underline-offset-2 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
                                                >{{ $field['value'] }}</a>
                                            @else
                                                {{ $field['value'] }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</section>
