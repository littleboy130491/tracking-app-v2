<?php

/**
 * File: app/Livewire/Customer/BillOfLadingDetail.php
 * Responsibility: Customer view of one shipment, its containers and journey.
 * What it does:
 * - Shows the customer-visible shipment facts, the shipment-level journey
 *   timeline and the list of containers (each opens the container page).
 * - For import shipments the customer can confirm the draft PIB or request a
 *   revision, which writes back to the B/L and the activity log. Once the draft
 *   is confirmed those actions are neither offered nor accepted: the guard is
 *   here, not only in the view.
 * How to use: route customer.bill-of-ladings.show.
 * How to extend: surface more customer-visible fields as they are added.
 */

namespace App\Livewire\Customer;

use App\Enums\DraftPibConfirmationStatus;
use App\Models\BillOfLading;
use App\Services\ActivityLogger;
use App\Services\ShipmentTimeline;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class BillOfLadingDetail extends Component
{
    public int $billOfLadingId;

    public string $revisionNotes = '';

    public ?string $message = null;

    public function mount(mixed $billOfLading): void
    {
        $id = $billOfLading instanceof BillOfLading ? $billOfLading->getKey() : (int) $billOfLading;

        $this->billOfLadingId = $id;

        // Reuses the scoped lookup so an out-of-scope id 404s.
        $this->billOfLading();
    }

    public function confirm(): void
    {
        $billOfLading = $this->billOfLading();

        abort_unless($billOfLading->isImport(), 403);

        // Already confirmed: keep it idempotent and leave the audit trail alone.
        if ($this->isDraftPibConfirmed($billOfLading)) {
            $this->message = 'This draft PIB is already confirmed.';

            return;
        }

        $billOfLading->update([
            'draft_pib_confirmation_status' => DraftPibConfirmationStatus::Confirmed,
            'draft_pib_confirmed_at' => now(),
            'draft_pib_confirmation_notes' => null,
        ]);

        app(ActivityLogger::class)->record(
            billOfLading: $billOfLading,
            event: 'draft_pib_confirmed',
            entityType: BillOfLading::class,
            entityId: $billOfLading->getKey(),
            newValues: ['draft_pib_confirmation_status' => DraftPibConfirmationStatus::Confirmed->value],
            customerSummary: 'Draft PIB confirmed by the customer.',
            customerVisible: true,
        );

        $this->message = 'Thank you — the draft PIB has been confirmed.';
    }

    public function requestRevision(): void
    {
        $billOfLading = $this->billOfLading();

        abort_unless($billOfLading->isImport(), 403);

        // A confirmed draft is final from the portal; the office has to reopen it.
        if ($this->isDraftPibConfirmed($billOfLading)) {
            throw ValidationException::withMessages([
                'revisionNotes' => 'This draft PIB is already confirmed. Please contact us if it has to change.',
            ]);
        }

        $this->validate(['revisionNotes' => ['required', 'string', 'max:1000']]);

        $billOfLading->update([
            'draft_pib_confirmation_status' => DraftPibConfirmationStatus::RevisionRequested,
            'draft_pib_confirmed_at' => null,
            'draft_pib_confirmation_notes' => $this->revisionNotes,
        ]);

        app(ActivityLogger::class)->record(
            billOfLading: $billOfLading,
            event: 'draft_pib_revision_requested',
            entityType: BillOfLading::class,
            entityId: $billOfLading->getKey(),
            newValues: ['notes' => $this->revisionNotes],
            customerSummary: 'The customer requested a revision of the draft PIB.',
            customerVisible: true,
        );

        $this->revisionNotes = '';
        $this->message = 'Your revision request has been sent.';
    }

    public function render(): View
    {
        $billOfLading = $this->billOfLading();

        return view('livewire.customer.bill-of-lading-detail', [
            'billOfLading' => $billOfLading,
            'containers' => $billOfLading->containers()->orderBy('container_number')->get(),
            'draftPibConfirmed' => $this->isDraftPibConfirmed($billOfLading),
            'timeline' => app(ShipmentTimeline::class)->forBillOfLading($billOfLading),
        ]);
    }

    private function isDraftPibConfirmed(BillOfLading $billOfLading): bool
    {
        return $billOfLading->draft_pib_confirmation_status === DraftPibConfirmationStatus::Confirmed;
    }

    private function billOfLading(): BillOfLading
    {
        $companyIds = auth()->user()->companies()->pluck('companies.id')->all();

        return BillOfLading::query()
            ->whereIn('company_id', $companyIds)
            ->with('company')
            ->findOrFail($this->billOfLadingId);
    }
}
