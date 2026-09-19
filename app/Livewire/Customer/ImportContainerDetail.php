<?php

/**
 * File: app/Livewire/Customer/ImportContainerDetail.php
 * Responsibility: Customer view of one import container plus its journey timeline.
 * What it does:
 * - Shows the container's import details and its chronological journey from
 *   the timeline service (container dates, voyage dates, visible logs).
 * - Scoping goes through the parent shipment, so a customer cannot open another
 *   company's container.
 * How to use: route customer.import-containers.show (opened in a new tab from
 *   the shipment page).
 * How to extend: add timeline entry fields via ShipmentTimelineEntry.
 */

namespace App\Livewire\Customer;

use App\Models\ImportContainer;
use App\Services\ShipmentTimeline;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class ImportContainerDetail extends Component
{
    public int $containerId;

    public function mount(mixed $importContainer): void
    {
        $this->containerId = $importContainer instanceof ImportContainer ? $importContainer->getKey() : (int) $importContainer;

        $this->container();
    }

    public function render(): View
    {
        $container = $this->container();

        return view('livewire.customer.import-container-detail', [
            'container' => $container,
            'timeline' => app(ShipmentTimeline::class)->forContainer($container),
        ]);
    }

    private function container(): ImportContainer
    {
        $query = ImportContainer::query()->with('shipment');

        // Draft shipments stay internal, so their containers do too.
        $query->whereHas('shipment', fn (Builder $query) => $query->visibleInPortal());

        // Admins open any container; customers stay scoped to their companies.
        if (! auth()->user()->canViewAllShipments()) {
            $companyIds = auth()->user()->companies()->pluck('companies.id')->all();
            $query->whereHas('shipment', fn (Builder $query) => $query->whereIn('company_id', $companyIds));
        }

        return $query->findOrFail($this->containerId);
    }
}
