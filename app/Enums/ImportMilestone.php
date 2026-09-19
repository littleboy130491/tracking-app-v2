<?php

/**
 * File: app/Enums/ImportMilestone.php
 * Responsibility: The ordered tracking milestones of an import shipment.
 * What it does:
 * - Holds one case per import milestone; `sequence()` returns them in order,
 *   dropping the SPJM branch unless the billing response was SPJM.
 * - `next()` / `previous()` move through that sequence; `unlocked()` tells the
 *   form whether a milestone has been reached so its fields are editable.
 * How to use: ImportShipment casts `current_milestone` to this enum.
 * How to extend: add a case, place it in sequence() and give it a label.
 */

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ImportMilestone: string implements HasLabel
{
    case DocumentReceived = 'document_received';
    case CheckingDocument = 'checking_document';
    case DraftPib = 'draft_pib';
    case DraftPibConfirmed = 'draft_pib_confirmed';
    case BillingIssued = 'billing_issued';
    case ThcPayment = 'thc_payment';
    case DoRelease = 'do_release';
    case BillingPayment = 'billing_payment';
    case BillingResponseReceived = 'billing_response_received';
    case DocumentsUploaded = 'documents_uploaded';
    case BehandlePayment = 'behandle_payment';
    case Inspection = 'inspection';
    case SppbReceived = 'sppb_received';
    case GateOutCy = 'gate_out_cy';
    case OnTheWayToConsignee = 'on_the_way_to_consignee';
    case ArrivedAtFactory = 'arrived_at_factory';
    case EmptyReturned = 'empty_returned';

    public function getLabel(): string
    {
        return match ($this) {
            self::DocumentReceived => 'Document received',
            self::CheckingDocument => 'Checking document',
            self::DraftPib => 'Draft PIB',
            self::DraftPibConfirmed => 'Draft PIB confirmed',
            self::BillingIssued => 'Billing issued',
            self::ThcPayment => 'THC payment',
            self::DoRelease => 'DO release',
            self::BillingPayment => 'Billing payment',
            self::BillingResponseReceived => 'Billing response received',
            self::DocumentsUploaded => 'Documents uploaded',
            self::BehandlePayment => 'Behandle payment',
            self::Inspection => 'Container inspection',
            self::SppbReceived => 'SPPB received',
            self::GateOutCy => 'Gate out CY',
            self::OnTheWayToConsignee => 'On the way to consignee',
            self::ArrivedAtFactory => 'Arrived at factory',
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
            self::DraftPibConfirmed,
            self::BillingIssued,
            self::ThcPayment,
            self::DoRelease,
            self::BillingPayment,
            self::BillingResponseReceived,
            self::DocumentsUploaded,
            self::BehandlePayment,
            self::Inspection,
            self::SppbReceived,
            self::GateOutCy,
            self::OnTheWayToConsignee,
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
     * The SPJM branch: extra import steps that only run when customs answers
     * the billing with SPJM.
     */
    public function isSpjmOnly(): bool
    {
        return match ($this) {
            self::DocumentsUploaded, self::BehandlePayment, self::Inspection, self::SppbReceived => true,
            default => false,
        };
    }
}
