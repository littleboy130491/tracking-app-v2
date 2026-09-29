<?php

/**
 * File: app/Models/Attachment.php
 * Responsibility: A file attached to a shipment or container, backed by Filament Curator.
 * What it does:
 * - Extends Curator's Media model, so uploads, storage, image conversions and
 *   the media picker all come from the package; this class only adds the links
 *   to the shipment/container, the category, the customer-portal visibility
 *   flag and who uploaded it.
 * - Registered as Curator's model in config/curator.php, so Curator's own
 *   resource and picker read and write this table.
 * - Files live on a private disk; every URL this model hands out points at
 *   the authorized /attachments route, and canBeViewedBy() decides who may
 *   fetch the file behind it.
 * How to use: `$shipment->attachments`, `$container->attachments`.
 * How to extend: add linkage columns in the curator-table migration.
 */

namespace App\Models;

use App\Enums\AttachmentCategory;
use App\Enums\ShipmentStatus;
use Awcodes\Curator\Models\Media;
use Awcodes\Curator\Observers\MediaObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Attributes are not inherited, so Curator's observer is re-declared here: it
// derives the file metadata and removes the file when the record is deleted.
#[ObservedBy([MediaObserver::class])]
#[Fillable([
    'export_shipment_id',
    'import_shipment_id',
    'export_container_id',
    'import_container_id',
    'category',
    'is_customer_visible',
    'uploaded_by',
])]
class Attachment extends Media
{
    /**
     * Merged with Curator's own casts rather than replacing them.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => AttachmentCategory::class,
            'is_customer_visible' => 'boolean',
        ];
    }

    /**
     * Every URL this model hands out — portal photos, Filament picker
     * thumbnails — points at the authorized serving route. The files live on
     * a private disk, so there is no public URL to fall back to; the resized
     * variants carry their size so grids never load the original.
     */
    public function url(): Attribute
    {
        return $this->servedUrl();
    }

    public function thumbnailUrl(): Attribute
    {
        return $this->servedUrl('thumb');
    }

    public function mediumUrl(): Attribute
    {
        return $this->servedUrl('medium');
    }

    public function largeUrl(): Attribute
    {
        return $this->servedUrl('large');
    }

    private function servedUrl(?string $size = null): Attribute
    {
        return Attribute::make(get: fn (): string => route('attachments.show', $size === null
            ? $this
            : ['attachment' => $this, 'size' => $size]));
    }

    /**
     * @return BelongsTo<ExportShipment, $this>
     */
    public function exportShipment(): BelongsTo
    {
        return $this->belongsTo(ExportShipment::class, 'export_shipment_id');
    }

    /**
     * @return BelongsTo<ImportShipment, $this>
     */
    public function importShipment(): BelongsTo
    {
        return $this->belongsTo(ImportShipment::class, 'import_shipment_id');
    }

    /**
     * @return BelongsTo<ExportContainer, $this>
     */
    public function exportContainer(): BelongsTo
    {
        return $this->belongsTo(ExportContainer::class, 'export_container_id');
    }

    /**
     * @return BelongsTo<ImportContainer, $this>
     */
    public function importContainer(): BelongsTo
    {
        return $this->belongsTo(ImportContainer::class, 'import_container_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * The linked shipment, whichever process it belongs to.
     */
    public function linkedShipment(): ExportShipment|ImportShipment|null
    {
        return $this->export_shipment_id ? $this->exportShipment : $this->importShipment;
    }

    /**
     * The linked container, if the file belongs to one.
     */
    public function linkedContainer(): ExportContainer|ImportContainer|null
    {
        return $this->export_container_id ? $this->exportContainer : $this->importContainer;
    }

    /**
     * Whether the given user may fetch this file through the serving route:
     * privileged staff see everything, other staff and customers stay inside
     * their companies, and customers only get files flagged for the portal on
     * shipments that already left the draft state.
     */
    public function canBeViewedBy(User $user): bool
    {
        $shipment = $this->linkedShipment();

        // Media with no shipment link (library orphans) stays staff-only.
        if ($shipment === null) {
            return $user->canViewAllShipments();
        }

        if (! $user->canViewAllShipments()
            && ! in_array((int) $shipment->company_id, $user->companyIds(), true)) {
            return false;
        }

        if ($user->isInternal()) {
            return true;
        }

        return $this->is_customer_visible && $shipment->status !== ShipmentStatus::Draft;
    }
}
