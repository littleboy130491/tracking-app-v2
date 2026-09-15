<?php

/**
 * File: app/Models/Attachment.php
 * Responsibility: A file attached to a shipment, backed by Filament Curator.
 * What it does:
 * - Extends Curator's Media model, so uploads, storage, image conversions and
 *   the media picker all come from the package; this class only adds the link
 *   to the shipment (B/L, container), the category, the customer-portal
 *   visibility flag and who uploaded it.
 * - Registered as Curator's model in config/curator.php, so Curator's own
 *   resource and picker read and write this table.
 * How to use: `$billOfLading->attachments`, `$container->attachments`.
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
    'bill_of_lading_id',
    'container_id',
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
     * @return BelongsTo<BillOfLading, $this>
     */
    public function billOfLading(): BelongsTo
    {
        return $this->belongsTo(BillOfLading::class);
    }

    /**
     * @return BelongsTo<Container, $this>
     */
    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
