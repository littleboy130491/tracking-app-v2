<?php

/**
 * File: app/Enums/ShipmentMilestone.php
 * Responsibility: The ordered tracking milestones a shipment moves through.
 * What it does:
 * - Holds one case per milestone; `sequence()` returns them in order for a
 *   shipment type, dropping the SPJM branch unless the billing response was
 *   SPJM.
 * - `next()` / `previous()` move through that sequence; `unlocked()` tells the
 *   form whether a milestone has been reached so its section is editable.
 * How to use: `ShipmentMilestone::sequence($bl->shipment_type, $bl->billing_response)`.
 * How to extend: add a case and place it in the type's sequence; a dedicated
 *   milestone model may replace this enum later.
 */

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ShipmentMilestone: string implements HasLabel
{
    case DocumentReceived = 'document_received';

    // Export
    case CheckingBookingOrder = 'checking_booking_order';
    case PickupEmptyContainer = 'pickup_empty_container';
    case OnTheWayToFactory = 'on_the_way_to_factory';
    case StuffingPebNpe = 'stuffing_peb_npe';
    case CheckingPebNpe = 'checking_peb_npe';
    case GateInCy = 'gate_in_cy';
    case FinalChecking = 'final_checking';

    // Import
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
            self::CheckingBookingOrder => 'Checking booking order',
            self::PickupEmptyContainer => 'Pick up empty container at depot',
            self::OnTheWayToFactory => 'Container on the way to factory',
            self::StuffingPebNpe => 'Stuffing at factory / PEB & NPE',
            self::CheckingPebNpe => 'Checking PEB & NPE',
            self::GateInCy => 'Gate in CY',
            self::FinalChecking => 'Final checking shipment details',
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
     * The milestones in order for a shipment type. The SPJM-only branch is
     * included only when the shipment's billing response was SPJM.
     *
     * @return list<self>
     */
    public static function sequence(ShipmentType $type, ?BillingResponse $response = null): array
    {
        $cases = match ($type) {
            ShipmentType::Export => [
                self::DocumentReceived,
                self::CheckingBookingOrder,
                self::PickupEmptyContainer,
                self::OnTheWayToFactory,
                self::StuffingPebNpe,
                self::CheckingPebNpe,
                self::GateInCy,
                self::FinalChecking,
            ],
            ShipmentType::Import => [
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
            ],
        };

        if ($response !== BillingResponse::Spjm) {
            $cases = array_values(array_filter($cases, fn (self $case): bool => ! $case->isSpjmOnly()));
        }

        return $cases;
    }

    public static function first(ShipmentType $type): self
    {
        return self::sequence($type)[0];
    }

    public static function next(ShipmentType $type, ?BillingResponse $response, ?self $current): ?self
    {
        $sequence = self::sequence($type, $response);
        $index = $current ? array_search($current, $sequence, true) : -1;

        return $sequence[($index === false ? -1 : $index) + 1] ?? null;
    }

    public static function previous(ShipmentType $type, ?BillingResponse $response, ?self $current): ?self
    {
        $sequence = self::sequence($type, $response);
        $index = $current ? array_search($current, $sequence, true) : -1;

        return $index > 0 ? $sequence[$index - 1] : null;
    }

    /**
     * Whether a shipment at `$current` may edit fields belonging to
     * `$required`. A null type or a required milestone outside the sequence
     * stays unlocked; a null current means the shipment sits at the start.
     */
    public static function unlocked(?ShipmentType $type, ?BillingResponse $response, ?self $current, self $required): bool
    {
        if (! $type) {
            return true;
        }

        $sequence = self::sequence($type, $response);
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
