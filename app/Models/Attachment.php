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
 * How to use: `$shipment->attachments`, `$container->attachments`.
 * How to extend: add linkage columns in the curator-table migration.
 */

namespace App\Models;

use App\Enums\AttachmentCategory;
use Awcodes\Curator\Models\Media;
use Awcodes\Curator\Observers\MediaObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
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
}
