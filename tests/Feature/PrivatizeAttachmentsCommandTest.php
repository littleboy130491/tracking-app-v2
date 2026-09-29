<?php

/**
 * File: tests/Feature/PrivatizeAttachmentsCommandTest.php
 * Responsibility: Guards the attachments:privatize move command.
 * What it does:
 * - Proves a public-disk file is copied to the private disk, verified, and
 *   only then removed, with the row's disk/visibility following.
 * - Proves a dry run, an already-private row, a missing source file and an
 *   unknown disk change nothing (or fail cleanly).
 * How to use: `php artisan test --filter=PrivatizeAttachmentsCommandTest`.
 * How to extend: add a case when attachments gain another source disk.
 */

namespace Tests\Feature;

use App\Models\Attachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivatizeAttachmentsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');
    }

    public function test_it_moves_a_public_attachment_to_the_private_disk(): void
    {
        $attachment = $this->attachment('public', 'public', 'demo/photo.jpg');
        Storage::disk('public')->put($attachment->path, 'photo-bytes');

        $this->artisan('attachments:privatize')->assertSuccessful();

        $attachment->refresh();

        $this->assertSame('local', $attachment->disk);
        $this->assertSame('private', $attachment->visibility);
        $this->assertSame('photo-bytes', Storage::disk('local')->get($attachment->path));
        $this->assertFalse(Storage::disk('public')->exists($attachment->path));
        $this->assertSame([], Storage::disk('public')->allFiles('demo'));
    }

    public function test_it_skips_attachments_already_on_the_private_disk(): void
    {
        $attachment = $this->attachment('local', 'private', 'attachments/photo.jpg');
        Storage::disk('local')->put($attachment->path, 'photo-bytes');

        $this->artisan('attachments:privatize')->assertSuccessful();

        $this->assertTrue(Storage::disk('local')->exists($attachment->path));
        $this->assertSame('local', $attachment->refresh()->disk);
    }

    public function test_a_dry_run_moves_nothing(): void
    {
        $attachment = $this->attachment('public', 'public', 'demo/photo.jpg');
        Storage::disk('public')->put($attachment->path, 'photo-bytes');

        $this->artisan('attachments:privatize', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame('public', $attachment->refresh()->disk);
        $this->assertTrue(Storage::disk('public')->exists($attachment->path));
        $this->assertFalse(Storage::disk('local')->exists($attachment->path));
    }

    public function test_a_missing_source_file_is_left_alone(): void
    {
        $attachment = $this->attachment('public', 'public', 'demo/missing.jpg');

        $this->artisan('attachments:privatize')->assertSuccessful();

        $this->assertSame('public', $attachment->refresh()->disk);
        $this->assertFalse(Storage::disk('local')->exists($attachment->path));
    }

    public function test_an_unknown_disk_fails(): void
    {
        $this->artisan('attachments:privatize', ['--disk' => 'nowhere'])->assertFailed();
    }

    private function attachment(string $disk, string $visibility, string $path): Attachment
    {
        return Attachment::query()->create([
            'disk' => $disk,
            'directory' => dirname($path),
            'visibility' => $visibility,
            'name' => basename($path),
            'path' => $path,
            'type' => 'image/jpeg',
            'ext' => 'jpg',
        ]);
    }
}
