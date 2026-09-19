{{-- File: resources/views/livewire/customer/partials/shipment-containers.blade.php
     Responsibility: The container list on a shipment detail page.
     What it does: lists the shipment's containers with identity, seal, status and
       the latest recorded event; each row opens the container page in a new tab.
     How to use: @include('livewire.customer.partials.shipment-containers', [
       'containers' => $containers, 'routeName' => '...', 'routeParam' => '...']);
     How to extend: add columns once more customer-visible container fields exist. --}}
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
                <th class="px-4 py-3">Latest update</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($containers as $container)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <a
                            href="{{ route($routeName, [$routeParam => $container->getKey()]) }}"
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
                    <td class="px-4 py-3">
                        {{ $container->latest_event ? \Illuminate\Support\Str::headline($container->latest_event) : '—' }}
                        @if ($container->latest_event_at)
                            <span class="block text-xs text-slate-500">{{ $container->latest_event_at->format('d M Y H:i') }}</span>
                        @endif
                    </td>
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
