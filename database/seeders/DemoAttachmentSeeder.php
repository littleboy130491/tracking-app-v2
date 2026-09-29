<?php

/**
 * File: database/seeders/DemoAttachmentSeeder.php
 * Responsibility: Seeds demo photos so the container pickers are not empty.
 * What it does:
 * - Generates small placeholder JPEGs on the public disk (GD, no bundled files)
 *   and registers them as media rows, then links one photo per picker slot to
 *   a couple of demo containers, both customer-visible and internal.
 * - Looks its containers up by number, so it depends on the shipment and
 *   container seeders running first.
 * - Idempotent: media are matched by `name`, and each container's photos are
 *   re-pointed with updateOrCreate, so re-running adds no duplicates.
 * How to use: run by DatabaseSeeder after the container seeders.
 * How to extend: add another container number or category to the map.
 */

namespace Database\Seeders;

use App\Enums\AttachmentCategory;
use App\Models\ExportContainer;
use App\Models\ImportContainer;
use Awcodes\Curator\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoAttachmentSeeder extends Seeder
{
    /** Photo picker slots to fill, in order. */
    private const CATEGORIES = [
        AttachmentCategory::DoorPhoto,
        AttachmentCategory::FloorPhoto,
        AttachmentCategory::SealPhoto,
        AttachmentCategory::EirPhoto,
        AttachmentCategory::AdditionalPhoto,
    ];

    public function run(): void
    {
        $this->attachToImport('CMAU7654321', customerVisible: true);
        $this->attachToExport('MSKU1234567', customerVisible: true);
        $this->attachToImport('CMAU7654323', customerVisible: false);
    }

    /**
     * Import container photo set.
     */
    private function attachToImport(string $containerNumber, bool $customerVisible): void
    {
        $container = ImportContainer::query()->where('container_number', $containerNumber)->first();

        if ($container === null) {
            return;
        }

        foreach (self::CATEGORIES as $category) {
            $media = $this->media($containerNumber, $category->value);

            $this->link($media, [
                'import_shipment_id' => $container->import_shipment_id,
                'import_container_id' => $container->getKey(),
                'export_shipment_id' => null,
                'export_container_id' => null,
                'category' => $category->value,
                'is_customer_visible' => $customerVisible,
            ]);
        }
    }

    /**
     * Export container photo set.
     */
    private function attachToExport(string $containerNumber, bool $customerVisible): void
    {
        $container = ExportContainer::query()->where('container_number', $containerNumber)->first();

        if ($container === null) {
            return;
        }

        foreach (self::CATEGORIES as $category) {
            $media = $this->media($containerNumber, $category->value);

            $this->link($media, [
                'import_shipment_id' => null,
                'import_container_id' => null,
                'export_shipment_id' => $container->export_shipment_id,
                'export_container_id' => $container->getKey(),
                'category' => $category->value,
                'is_customer_visible' => $customerVisible,
            ]);
        }
    }

    /**
     * Create (once) the placeholder image and its media row for a slot. The
     * media `name` is deterministic so re-running reuses the same row.
     */
    private function media(string $containerNumber, string $category): Media
    {
        $name = "demo-{$containerNumber}-{$category}.jpg";

        $media = Media::query()->firstOrNew(['name' => $name]);

        if ($media->exists) {
            return $media;
        }

        $path = 'demo/'.$name;

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, $this->placeholderJpeg());
        }

        $media->fill([
            'disk' => 'public',
            'directory' => 'demo',
            'visibility' => 'public',
            'path' => $path,
            'width' => 64,
            'height' => 64,
            'size' => Storage::disk('public')->size($path),
            'type' => 'image/jpeg',
            'ext' => 'jpg',
            'alt' => Str::headline(str_replace('_', ' ', $category)),
            'title' => Str::headline(str_replace('_', ' ', $category)),
        ])->save();

        return $media;
    }

    /**
     * Point a media row at a container/category, without touching the file.
     *
     * @param  array<string, mixed>  $links
     */
    private function link(Media $media, array $links): void
    {
        $media->forceFill($links)->save();
    }

    /**
     * A 64x64 grey JPEG with a diagonal marker, built with GD so the seeder
     * needs no bundled binary asset.
     */
    private function placeholderJpeg(): string
    {
        $image = imagecreatetruecolor(64, 64);

        $background = imagecolorallocate($image, 226, 232, 240);
        $marker = imagecolorallocate($image, 148, 163, 184);

        imagefilledrectangle($image, 0, 0, 63, 63, $background);
        imageline($image, 0, 0, 63, 63, $marker);
        imageline($image, 63, 0, 0, 63, $marker);

        ob_start();
        imagejpeg($image, null, 70);
        $contents = (string) ob_get_clean();

        imagedestroy($image);

        return $contents;
    }
}
