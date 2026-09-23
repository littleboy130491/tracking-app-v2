<?php

/**
 * File: app/Livewire/Customer/ExportShipmentDetail.php
 * Responsibility: Customer view of one export shipment, its containers and journey.
 * What it does:
 * - Shows the customer-visible shipment facts, the sailing card (fields
 *   lifted from reached timeline steps), the shipment-level journey
 *   timeline and the container accordions (summary + per-step progress
 *   from ShipmentTimeline::forContainers()).
 * How to use: route customer.export-shipments.show.
 * How to extend: surface more customer-visible fields as they are added.
 */

namespace App\Livewire\Customer;

use App\Models\ExportShipment;
use App\Services\ShipmentTimeline;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class ExportShipmentDetail extends Component
{
    public int $shipmentId;

    public function mount(mixed $exportShipment): void
    {
        $this->shipmentId = $exportShipment instanceof ExportShipment ? $exportShipment->getKey() : (int) $exportShipment;

        // Reuses the scoped lookup so an out-of-scope id 404s.
        $this->shipment();
    }

    public function render(): View
    {
        $shipment = $this->shipment();
        $containers = $shipment->containers()
            ->with([
                'attachments' => fn ($query) => $query->where('is_customer_visible', true),
            ])
            ->orderBy('container_number')
            ->get();

        $timeline = app(ShipmentTimeline::class);
        $entries = $timeline->forShipment($shipment);

        return view('livewire.customer.export-shipment-detail', [
            'shipment' => $shipment,
            'containers' => $containers,
            'containerProgress' => $timeline->forContainers($shipment, $containers),
            'timeline' => $entries,
            'sailing' => $timeline->sailingInformation($entries),
        ]);
    }

    private function shipment(): ExportShipment
    {
        $query = ExportShipment::query()->with('company')->visibleInPortal();

        // Admins open any shipment; customers stay scoped to their companies.
        if (! auth()->user()->canViewAllShipments()) {
            $companyIds = auth()->user()->companies()->pluck('companies.id')->all();
            $query->whereIn('company_id', $companyIds);
        }

        return $query->findOrFail($this->shipmentId);
    }
}
