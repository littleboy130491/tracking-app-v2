<?php

/**
 * File: app/Livewire/Customer/ImportShipmentDetail.php
 * Responsibility: Customer view of one import shipment, its containers and journey.
 * What it does:
 * - Shows the customer-visible shipment facts, the shipment-level journey
 *   timeline and the list of containers (each opens the container page).
 * - The customer can confirm the draft PIB or request a revision, which
 *   writes back to the shipment and the activity log. Once the draft is
 *   confirmed those actions are neither offered nor accepted: the guard is
 *   here, not only in the view.
 * How to use: route customer.import-shipments.show.
 * How to extend: surface more customer-visible fields as they are added.
 */

namespace App\Livewire\Customer;

use App\Enums\DraftPibConfirmationStatus;
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

        $shipment->update([
            'draft_pib_confirmation_status' => DraftPibConfirmationStatus::Confirmed,
            'draft_pib_confirmed_at' => now(),
            'draft_pib_confirmation_notes' => null,
        ]);

        app(ActivityLogger::class)->record(
            shipment: $shipment,
            event: 'draft_pib_confirmed',
            entityType: ImportShipment::class,
            entityId: $shipment->getKey(),
            newValues: ['draft_pib_confirmation_status' => DraftPibConfirmationStatus::Confirmed->value],
            customerSummary: 'Draft PIB confirmed by the customer.',
            customerVisible: true,
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

        $shipment->update([
            'draft_pib_confirmation_status' => DraftPibConfirmationStatus::RevisionRequested,
            'draft_pib_confirmed_at' => null,
            'draft_pib_confirmation_notes' => $this->revisionNotes,
        ]);

        app(ActivityLogger::class)->record(
            shipment: $shipment,
            event: 'draft_pib_revision_requested',
            entityType: ImportShipment::class,
            entityId: $shipment->getKey(),
            newValues: ['notes' => $this->revisionNotes],
            customerSummary: 'The customer requested a revision of the draft PIB.',
            customerVisible: true,
        );

        $this->revisionNotes = '';
        $this->message = 'Your revision request has been sent.';
    }

    public function render(): View
    {
        $shipment = $this->shipment();

        return view('livewire.customer.import-shipment-detail', [
            'shipment' => $shipment,
            'containers' => $shipment->containers()->orderBy('container_number')->get(),
            'draftPibConfirmed' => $this->isDraftPibConfirmed($shipment),
            'timeline' => app(ShipmentTimeline::class)->forShipment($shipment),
        ]);
    }

    private function isDraftPibConfirmed(ImportShipment $shipment): bool
    {
        return $shipment->draft_pib_confirmation_status === DraftPibConfirmationStatus::Confirmed;
    }

    private function shipment(): ImportShipment
    {
        $query = ImportShipment::query()->with('company');

        // Admins open any shipment; customers stay scoped to their companies.
        if (! auth()->user()->canViewAllShipments()) {
            $companyIds = auth()->user()->companies()->pluck('companies.id')->all();
            $query->whereIn('company_id', $companyIds);
        }

        return $query->findOrFail($this->shipmentId);
    }
}
