<?php

/**
 * File: app/Livewire/Customer/ImportShipmentDetail.php
 * Responsibility: Customer view of one import shipment, its containers and journey.
 * What it does:
 * - Shows the customer-visible shipment facts, the sailing card (fields
 *   lifted from reached timeline steps), the shipment-level journey
 *   timeline and the container accordions (summary + per-step progress
 *   from ShipmentTimeline::forContainers()).
 * - The customer can confirm the draft PIB (sets the confirmation checklist)
 *   or request a revision, which stores a customer-authored note on the
 *   shipment and writes the activity log. Once the draft is confirmed those
 *   actions are neither offered nor accepted: the guard is here, not only
 *   in the view.
 * - Lists the customer's own notes on the shipment; notes written by anyone
 *   else (e.g. the office) stay internal and are never shown here.
 * How to use: route customer.import-shipments.show.
 * How to extend: surface more customer-visible fields as they are added.
 */

namespace App\Livewire\Customer;

use App\Models\ImportShipment;
use App\Services\ActivityLogger;
use App\Services\ShipmentTimeline;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class ImportShipmentDetail extends Component
{
    public int $shipmentId;

    public string $revisionNotes = '';

    public ?string $message = null;

    public function mount(mixed $importShipment): void
    {
        $this->shipmentId = $importShipment instanceof ImportShipment ? $importShipment->getKey() : (int) $importShipment;

        // Reuses the scoped lookup so an out-of-scope id 404s.
        $this->shipment();
    }

    public function confirm(): void
    {
        $shipment = $this->shipment();

        // Already confirmed: keep it idempotent and leave the audit trail alone.
        if ($this->isDraftPibConfirmed($shipment)) {
            $this->message = 'This draft PIB is already confirmed.';

            return;
        }

        $shipment->update(['confirmation_checklist' => true]);

        app(ActivityLogger::class)->record(
            shipment: $shipment,
            event: 'draft_pib_confirmed',
            entityType: ImportShipment::class,
            entityId: $shipment->getKey(),
            newValues: ['confirmation_checklist' => true],
            customerSummary: 'Draft PIB confirmed by the customer.',
        );

        $this->message = 'Thank you — the draft PIB has been confirmed.';
    }

    public function requestRevision(): void
    {
        $shipment = $this->shipment();

        // A confirmed draft is final from the portal; the office has to reopen it.
        if ($this->isDraftPibConfirmed($shipment)) {
            throw ValidationException::withMessages([
                'revisionNotes' => 'This draft PIB is already confirmed. Please contact us if it has to change.',
            ]);
        }

        $this->validate(['revisionNotes' => ['required', 'string', 'max:1000']]);

        // The request is stored as a customer-authored note on the shipment, so
        // the office sees it in the shipment's Notes tab and the customer can
        // see their own messages on this page.
        $shipment->notes()->create([
            'body' => $this->revisionNotes,
            'author_id' => auth()->id(),
        ]);

        app(ActivityLogger::class)->record(
            shipment: $shipment,
            event: 'draft_pib_revision_requested',
            entityType: ImportShipment::class,
            entityId: $shipment->getKey(),
            newValues: ['notes' => $this->revisionNotes],
            customerSummary: 'The customer requested a revision of the draft PIB.',
        );

        $this->revisionNotes = '';
        $this->message = 'Your revision request has been sent.';
    }

    public function render(): View
    {
        $shipment = $this->shipment();
        $containers = $shipment->containers()
            ->with([
                'attachments' => fn ($query) => $query->where('is_customer_visible', true),
                'hsCodes',
            ])
            ->orderBy('container_number')
            ->get();

        $timeline = app(ShipmentTimeline::class);
        $entries = $timeline->forShipment($shipment);

        return view('livewire.customer.import-shipment-detail', [
            'shipment' => $shipment,
            'containers' => $containers,
            'containerProgress' => $timeline->forContainers($shipment, $containers),
            'draftPibConfirmed' => $this->isDraftPibConfirmed($shipment),
            // Only the customer's own notes: office notes on the shipment
            // are internal and must not leak into the portal.
            'ownNotes' => $shipment->notes()->where('author_id', auth()->id())->latest()->get(),
            'timeline' => $entries,
            'sailing' => $timeline->sailingInformation($entries),
        ]);
    }

    private function isDraftPibConfirmed(ImportShipment $shipment): bool
    {
        return (bool) $shipment->confirmation_checklist;
    }

    private function shipment(): ImportShipment
    {
        $query = ImportShipment::query()->with('company')->visibleInPortal();

        // Admins open any shipment; customers stay scoped to their companies.
        if (! auth()->user()->canViewAllShipments()) {
            $companyIds = auth()->user()->companies()->pluck('companies.id')->all();
            $query->whereIn('company_id', $companyIds);
        }

        return $query->findOrFail($this->shipmentId);
    }
}
