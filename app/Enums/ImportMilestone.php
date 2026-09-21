<?php

/**
 * File: app/Enums/ImportMilestone.php
 * Responsibility: The ordered tracking milestones of an import shipment.
 * What it does:
 * - Holds one case per import milestone (IMPORT.md); `sequence()` returns them
 *   in order, dropping the SPJM branch unless the billing response was SPJM.
 * - `next()` / `previous()` move through that sequence; `unlocked()` tells the
 *   form whether a milestone has been reached so its fields are editable.
 * How to use: ImportShipment casts `current_milestone` to this enum.
 * How to extend: add a case, place it in sequence() and give it a label.
 */

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ImportMilestone: string implements HasLabel
{
    // Process 1 — document intake.
    case DocumentReceived = 'document_received';
    case CheckingDocument = 'checking_document';
    // Process 2 — PIB, billing and customs.
    case DraftPib = 'draft_pib';
    case CheckingDraftPib = 'checking_draft_pib';
    case WaitingConfirmation = 'waiting_confirmation';
    case FinalSendingPib = 'final_sending_pib';
    case ThcPayment = 'thc_payment';
    case WaitingReleaseDo = 'waiting_release_do';
    case DoRelease = 'do_release';
    case BillingPayment = 'billing_payment';
    case ResponseBilling = 'response_billing';
    case UploadAllDocument = 'upload_all_document';
    case WaitingProcessBehandle = 'waiting_process_behandle';
    case PaymentBehandle = 'payment_behandle';
    case Inspection = 'inspection';
    case WaitingChangeStatusSppb = 'waiting_change_status_sppb';
    case ContainerShippingSchedule = 'container_shipping_schedule';
    // Process 3 — delivery and return.
    case GateOutCy = 'gate_out_cy';
    case OnTheWayToFactory = 'on_the_way_to_factory';
    case ArrivedAtFactory = 'arrived_at_factory';
    case EmptyReturned = 'empty_returned';

    public function getLabel(): string
    {
        return match ($this) {
            self::DocumentReceived => 'Document received',
            self::CheckingDocument => 'Checking document',
            self::DraftPib => 'Draft PIB',
            self::CheckingDraftPib => 'Checking draft PIB to importir',
            self::WaitingConfirmation => 'Waiting confirmation from customer',
            self::FinalSendingPib => 'Final sending PIB to custom (issuing billing)',
            self::ThcPayment => 'Process payment THC',
            self::WaitingReleaseDo => 'Waiting release DO',
            self::DoRelease => 'DO release',
            self::BillingPayment => 'Payment billing',
            self::ResponseBilling => 'Response billing',
            self::UploadAllDocument => 'Upload all document',
            self::WaitingProcessBehandle => 'Waiting process bahandle',
            self::PaymentBehandle => 'Payment bahandle',
            self::Inspection => 'Container inspection',
            self::WaitingChangeStatusSppb => 'Waiting change status SPJM to SPPB',
            self::ContainerShippingSchedule => 'Container shipping schedule',
            self::GateOutCy => 'Gate out from inbound terminal',
            self::OnTheWayToFactory => 'Container on the way factory',
            self::ArrivedAtFactory => 'Container arrived in factory',
            self::EmptyReturned => 'Empty container returned',
        };
    }

    /**
     * The milestones in order. The SPJM-only branch is included only when the
     * shipment's billing response was SPJM.
     *
     * @return list<self>
     */
    public static function sequence(?BillingResponse $response = null): array
    {
        $cases = [
            self::DocumentReceived,
            self::CheckingDocument,
            self::DraftPib,
            self::CheckingDraftPib,
            self::WaitingConfirmation,
            self::FinalSendingPib,
            self::ThcPayment,
            self::WaitingReleaseDo,
            self::DoRelease,
            self::BillingPayment,
            self::ResponseBilling,
            self::UploadAllDocument,
            self::WaitingProcessBehandle,
            self::PaymentBehandle,
            self::Inspection,
            self::WaitingChangeStatusSppb,
            self::ContainerShippingSchedule,
            self::GateOutCy,
            self::OnTheWayToFactory,
            self::ArrivedAtFactory,
            self::EmptyReturned,
        ];

        if ($response !== BillingResponse::Spjm) {
            $cases = array_values(array_filter($cases, fn (self $case): bool => ! $case->isSpjmOnly()));
        }

        return $cases;
    }

    public static function first(?BillingResponse $response = null): self
    {
        return self::sequence($response)[0];
    }

    public static function next(?BillingResponse $response, ?self $current): ?self
    {
        $sequence = self::sequence($response);
        $index = $current ? array_search($current, $sequence, true) : -1;

        return $sequence[($index === false ? -1 : $index) + 1] ?? null;
    }

    public static function previous(?BillingResponse $response, ?self $current): ?self
    {
        $sequence = self::sequence($response);
        $index = $current ? array_search($current, $sequence, true) : -1;

        return $index > 0 ? $sequence[$index - 1] : null;
    }

    /**
     * Whether a shipment at `$current` may edit fields belonging to
     * `$required`. A null current means the shipment sits at the start; a
     * required milestone outside the sequence stays unlocked.
     */
    public static function unlocked(?BillingResponse $response, ?self $current, self $required): bool
    {
        $sequence = self::sequence($response);
        $requiredIndex = array_search($required, $sequence, true);

        if ($requiredIndex === false) {
            return true;
        }

        $currentIndex = $current ? array_search($current, $sequence, true) : 0;

        return $currentIndex !== false && $currentIndex >= $requiredIndex;
    }

    /**
     * The SPJM branch: the additional customs steps that only run when the
     * billing response was SPJM. The branch itself is information only — the
     * form shows a notice while the response is SPJM; it is not a step.
     */
    public function isSpjmOnly(): bool
    {
        return match ($this) {
            self::UploadAllDocument,
            self::WaitingProcessBehandle,
            self::PaymentBehandle,
            self::Inspection,
            self::WaitingChangeStatusSppb => true,
            default => false,
        };
    }
}
