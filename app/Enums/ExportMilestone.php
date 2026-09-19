<?php

/**
 * File: app/Enums/ExportMilestone.php
 * Responsibility: The ordered tracking milestones of an export shipment.
 * What it does:
 * - Holds one case per export milestone; `sequence()` returns them in order.
 * - `next()` / `previous()` move through that sequence; `unlocked()` tells the
 *   form whether a milestone has been reached so its fields are editable.
 * How to use: ExportShipment casts `current_milestone` to this enum.
 * How to extend: add a case, place it in sequence() and give it a label.
 */

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ExportMilestone: string implements HasLabel
{
    case DocumentReceived = 'document_received';
    case CheckingBookingOrder = 'checking_booking_order';
    case PickupEmptyContainer = 'pickup_empty_container';
    case OnTheWayToFactory = 'on_the_way_to_factory';
    case StuffingPebNpe = 'stuffing_peb_npe';
    case CheckingPebNpe = 'checking_peb_npe';
    case GateInCy = 'gate_in_cy';
    case FinalChecking = 'final_checking';

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
        };
    }

    /**
     * The milestones in order. The `$response` parameter keeps the signature
     * aligned with ImportMilestone; export has no SPJM branch.
     *
     * @return list<self>
     */
    public static function sequence(?BillingResponse $response = null): array
    {
        return [
            self::DocumentReceived,
            self::CheckingBookingOrder,
            self::PickupEmptyContainer,
            self::OnTheWayToFactory,
            self::StuffingPebNpe,
            self::CheckingPebNpe,
            self::GateInCy,
            self::FinalChecking,
        ];
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
}
