{{-- File: resources/views/livewire/customer/partials/shipment-containers.blade.php
     Responsibility: Expandable container table inside the B/L detail card.
     What it does: each container is a <details> row with plain table columns
       (number, latest event/time, tracking position with the Track live link
       below it when a tracking URL exists, seal); the expanded body holds
       the photo strip (when present) and the per-step journey from the
       container-steps partial.
     How to use: @include('livewire.customer.partials.shipment-containers',
       ['containers' => $containers, 'containerProgress' => $containerProgress]).
     How to extend: step data comes from App\Services\ContainerProgress
       (ShipmentTimeline::forContainers); add photo slots in $photoSlots. --}}
@if ($containers->isNotEmpty())
<div class="overflow-x-auto">
    <div class="min-w-[820px]">
    <div class="grid grid-cols-[1.3fr_1.5fr_1fr_0.8fr_2rem] gap-0 border-b border-slate-200 text-left" aria-hidden="true">
        <span class="px-4 py-2.5 text-sm font-semibold text-slate-900 sm:px-6">Container No.</span>
        <span class="border-l border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-900">Latest Event Status/ Time</span>
        <span class="border-l border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-900">Tracking position</span>
        <span class="border-l border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-900">Seal No.</span>
        <span class="px-4 py-2.5"></span>
    </div>
    <div class="divide-y divide-slate-200">
        @foreach ($containers as $container)
            @php
                $progress = $containerProgress[$container->getKey()];
                $status = $progress->status;
                $latest = $progress->latestReached();
                $stateText = match (true) {
                    $status === \App\Enums\ContainerStatus::Cancelled => 'Cancelled',
                    $latest === null => 'Not started',
                    default => $latest->title,
                };
                $sizeLabel = match ((string) $container->size) {
                    '20' => '20 ft',
                    '40' => '40 ft',
                    '45' => '45 ft',
                    default => $container->size,
                };
                $photoSlots = [
                    \App\Enums\AttachmentCategory::DoorPhoto->value => 'Photo Door',
                    \App\Enums\AttachmentCategory::FloorPhoto->value => 'Photo Floor',
                    \App\Enums\AttachmentCategory::SealPhoto->value => 'Photo Seal',
                    \App\Enums\AttachmentCategory::EirPhoto->value => 'Photo EIR',
                    \App\Enums\AttachmentCategory::AdditionalPhoto->value => 'Additional Photos',
                ];
                $photos = $container->attachments->filter(
                    fn ($attachment) => array_key_exists($attachment->category?->value ?? '', $photoSlots),
                );
                $hasTrackLink = $progress->trackingUrl && $status === \App\Enums\ContainerStatus::InProgress;
            @endphp
            {{-- wire:ignore.self keeps the open attribute untouched when Livewire re-renders. --}}
            <details class="group" wire:key="container-{{ $container->getKey() }}" wire:ignore.self>
                <summary class="cursor-pointer list-none transition-colors hover:bg-slate-50 focus-visible:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-500 [&::-webkit-details-marker]:hidden">
                    @php
                        $trackingPosition = $container->getAttribute('tracking_position') ?: '—';
                        $sealNumber = $container->getAttribute('seal_number') ?: '—';
                        $subline = collect([$sizeLabel, ...$progress->chips])->filter(filled(...))->implode(' · ');
                    @endphp
                    <span class="grid grid-cols-[1.3fr_1.5fr_1fr_0.8fr_2rem] items-start gap-0">
                        <span class="min-w-0 px-4 py-3 sm:px-6">
                            <span class="block truncate font-medium text-brand-700 underline underline-offset-2">{{ $container->container_number }}</span>
                            @if (filled($subline))
                                <span class="mt-0.5 block truncate text-xs text-slate-500">{{ $subline }}</span>
                            @endif
                        </span>
                        <span class="min-w-0 border-l border-slate-100 px-4 py-3">
                            <span class="block text-sm text-slate-800">{{ $stateText }}</span>
                            @if ($latest?->occurredAt)
                                <span class="mt-0.5 block text-xs text-slate-500">{{ $latest->occurredAt }}</span>
                            @endif
                        </span>
                        <span class="min-w-0 border-l border-slate-100 px-4 py-3">
                            <span class="block break-words text-sm text-slate-800">{{ $trackingPosition }}</span>
                            @if ($hasTrackLink)
                                <a
                                    href="{{ $progress->trackingUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="Track this container live (opens in a new tab)"
                                    class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-brand-700 underline-offset-2 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
                                >
                                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 3h6v6M17 3l-8 8M7 5H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-2" />
                                    </svg>
                                    Track live
                                </a>
                            @endif
                        </span>
                        <span class="min-w-0 border-l border-slate-100 px-4 py-3">
                            <span class="block break-words text-sm text-slate-800">{{ $sealNumber }}</span>
                        </span>
                        <span class="flex justify-end border-l border-slate-100 px-4 py-3">
                            <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </span>
                </summary>
                <div class="grid gap-4 border-t border-slate-100 bg-slate-50/60 px-4 py-4 sm:px-6">
                    @if ($photos->isNotEmpty())
                    <section class="rounded-lg bg-white p-4 ring-1 ring-slate-100">
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wide text-slate-500">Photos</h4>
                            <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                                @foreach ($photoSlots as $category => $photoLabel)
                                    @foreach ($photos->filter(fn ($photo) => $photo->category?->value === $category) as $photo)
                                        <a
                                            href="{{ $photo->url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label="{{ $photoLabel }} (opens in a new tab)"
                                            class="group/photo block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
                                        >
                                            <img
                                                src="{{ $photo->url }}"
                                                alt="{{ $photo->alt ?: $photoLabel }}"
                                                loading="lazy"
                                                class="h-28 w-full rounded-md border border-slate-200 object-cover transition group-hover/photo:border-brand-400"
                                            >
                                            <span class="mt-1 block break-words text-xs text-slate-500">{{ $photoLabel }}</span>
                                        </a>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </section>
                    @endif

                    @include('livewire.customer.partials.container-steps', ['progress' => $progress])
                </div>
            </details>
        @endforeach
    </div>
    </div>
    </div>
@endif
