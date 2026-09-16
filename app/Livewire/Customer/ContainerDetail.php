<?php

/**
 * File: app/Livewire/Customer/ContainerDetail.php
 * Responsibility: Customer view of one container plus its journey timeline.
 * What it does:
 * - Shows the container's details and its chronological journey from the
 *   timeline service (container dates, voyage dates, visible logs).
 * - Scoping goes through the parent shipment, so a customer cannot open another
 *   company's container.
 * How to use: route customer.containers.show (opened in a new tab from the
 *   shipment page).
 * How to extend: add timeline entry fields via ShipmentTimelineEntry.
 */

namespace App\Livewire\Customer;

use App\Models\Container;
use App\Services\ShipmentTimeline;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class ContainerDetail extends Component
{
    public int $containerId;

    public function mount(mixed $container): void
    {
        $id = $container instanceof Container ? $container->getKey() : (int) $container;

        $this->containerId = $id;

        $this->container();
    }

    public function render(): View
    {
        $container = $this->container();

        return view('livewire.customer.container-detail', [
            'container' => $container,
            'timeline' => app(ShipmentTimeline::class)->forContainer($container),
        ]);
    }

    private function container(): Container
    {
        $companyIds = auth()->user()->companies()->pluck('companies.id')->all();

        return Container::query()
            ->whereHas('billOfLading', fn (Builder $query) => $query->whereIn('company_id', $companyIds))
            ->with('billOfLading')
            ->findOrFail($this->containerId);
    }
}
