{{-- File: resources/views/livewire/customer/partials/shipment-containers.blade.php
     Responsibility: Expandable container cards on a shipment detail page.
     What it does: each container is a <details> row with its number, size,
       seal/weight chips, derived status pill, latest reached step and a mini
       progress bar; the expanded body holds a summary card (tiles, Track live
       link, photo strip) and the per-step journey from the container-steps
       partial.
     How to use: @include('livewire.customer.partials.shipment-containers',
       ['containers' => $containers, 'containerProgress' => $containerProgress]).
     How to extend: step/summary data comes from App\Services\ContainerProgress
       (ShipmentTimeline::forContainers); add photo slots in $photoSlots. --}}
@if ($containers->isNotEmpty())
<div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm">
    <div class="border-b border-slate-200 px-4 py-3">
        <h2 class="flex items-center gap-2 font-semibold text-slate-900">
            Containers
            <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700">{{ $containers->count() }}</span>
        </h2>
    </div>
    <div class="divide-y divide-slate-100">
        @foreach ($containers as $container)
            @php
                $progress = $containerProgress[$container->getKey()];
                $status = $progress->status;
                $latest = $progress->latestReached();
                $stateText = match (true) {
                    $status === \App\Enums\ContainerStatus::Cancelled => 'Cancelled',
                    $latest === null => 'Not started',
                    default => 'Latest: '.$latest->title,
                };
                $pillClasses = match ($status) {
                    \App\Enums\ContainerStatus::Pending => 'bg-slate-100 text-slate-600',
                    \App\Enums\ContainerStatus::InProgress => 'bg-amber-100 text-amber-700',
                    \App\Enums\ContainerStatus::Completed => 'bg-accent-100 text-accent-600',
                    \App\Enums\ContainerStatus::Cancelled => 'bg-red-100 text-red-700',
                };
                $borderClass = match ($status) {
                    \App\Enums\ContainerStatus::Pending => 'border-l-slate-300',
                    \App\Enums\ContainerStatus::InProgress => 'border-l-amber-400',
                    \App\Enums\ContainerStatus::Completed => 'border-l-accent-500',
                    \App\Enums\ContainerStatus::Cancelled => 'border-l-red-400',
                };
                $percent = $progress->totalSteps() > 0
                    ? (int) round($progress->reachedCount() / $progress->totalSteps() * 100)
                    : 0;
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
            @endphp
            {{-- wire:ignore.self keeps the open attribute untouched when Livewire re-renders. --}}
            <details class="group border-l-4 {{ $borderClass }}" wire:key="container-{{ $container->getKey() }}" wire:ignore.self>
                <summary class="cursor-pointer list-none px-4 py-3 transition-colors hover:bg-slate-50 focus-visible:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-500 [&::-webkit-details-marker]:hidden">
                    <span class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                        <span class="flex min-w-0 flex-wrap items-center gap-x-2.5 gap-y-1.5">
                            <svg class="h-5 w-5 shrink-0 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <rect x="3" y="7" width="18" height="10" rx="1" />
                                <path stroke-linecap="round" d="M7 7v10M11 7v10M15 7v10M19 7v10" />
                            </svg>
                            <span class="font-semibold text-slate-900">{{ $container->container_number }}</span>
                            @if (filled($sizeLabel))
                                <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-600">{{ $sizeLabel }}</span>
                            @endif
                            @foreach ($progress->chips as $chip)
                                <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-600">{{ $chip }}</span>
                            @endforeach
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $pillClasses }}">{{ $status->label() }}</span>
                        </span>
                        <span class="flex items-center gap-3">
                            <span class="grid gap-1">
                                <span class="text-xs font-medium text-slate-600">
                                    {{ $stateText }}
                                    @if ($latest?->occurredAt)
                                        <span class="block font-normal text-slate-400">{{ $latest->occurredAt }}</span>
                                    @endif
                                </span>
                                <span class="flex items-center gap-2">
                                    <span
                                        class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100"
                                        role="progressbar"
                                        aria-valuemin="0"
                                        aria-valuemax="{{ $progress->totalSteps() }}"
                                        aria-valuenow="{{ $progress->reachedCount() }}"
                                        aria-label="Container progress"
                                    >
                                        <span class="block h-full rounded-full {{ $progress->isComplete() ? 'bg-accent-500' : 'bg-brand-500' }}" style="width: {{ $percent }}%"></span>
                                    </span>
                                    <span class="whitespace-nowrap text-xs text-slate-400">{{ $progress->reachedCount() }} of {{ $progress->totalSteps() }} steps</span>
                                </span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </span>
                </summary>
                <div class="grid gap-4 border-t border-slate-100 bg-slate-50/60 px-4 py-4 sm:px-6">
                    <section class="rounded-lg bg-white p-4 ring-1 ring-slate-100">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold text-slate-900">Container summary</h3>
                            @if ($progress->trackingUrl && $status === \App\Enums\ContainerStatus::InProgress)
                                <a
                                    href="{{ $progress->trackingUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="Track this container live (opens in a new tab)"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 3h6v6M17 3l-8 8M7 5H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-2" />
                                    </svg>
                                    Track live
                                </a>
                            @endif
                        </div>
                        @if ($progress->summary !== [])
                            <dl class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($progress->summary as $tile)
                                    <div @class(['min-w-0 rounded-md bg-slate-50 px-3 py-2', 'sm:col-span-2 lg:col-span-3' => $tile['wide'] ?? false])>
                                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $tile['label'] }}</dt>
                                        <dd class="mt-0.5 break-words text-sm font-medium text-slate-900">{{ $tile['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                        @if ($photos->isNotEmpty())
                            <div class="mt-4">
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
                        @endif
                        @if ($progress->summary === [] && $photos->isEmpty())
                            <p class="mt-3 text-sm text-slate-400">No container details published yet.</p>
                        @endif
                    </section>

                    @include('livewire.customer.partials.container-steps', ['progress' => $progress])
                </div>
            </details>
        @endforeach
    </div>
</div>
@endif
