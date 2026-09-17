<?php

/**
 * File: app/Enums/AttachmentCategory.php
 * Responsibility: Allowed attachment categories.
 * What it does:
 * - Restricts uploads to the categories listed in migration_plan.md §10.
 * How to use: Attachment casts `category` to this enum.
 * How to extend: Add a case plus its label; no other change is required.
 */

namespace App\Enums;

use App\Enums\Concerns\HasSelectOptions;

enum AttachmentCategory: string
{
    use HasSelectOptions;

    case DoorPhoto = 'door_photo';
    case FloorPhoto = 'floor_photo';
    case EirPhoto = 'eir_photo';
    case SealPhoto = 'seal_photo';
    case SupportingDocument = 'supporting_document';
    case AdditionalPhoto = 'additional_photo';

    public function label(): string
    {
        return match ($this) {
            self::DoorPhoto => 'Door Photo',
            self::FloorPhoto => 'Floor Photo',
            self::EirPhoto => 'EIR Photo',
            self::SealPhoto => 'Seal Photo',
            self::SupportingDocument => 'Supporting Document',
            self::AdditionalPhoto => 'Additional Photo',
        };
    }
}
