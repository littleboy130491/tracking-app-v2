<?php

/**
 * File: app/Console/Commands/PrivatizeAttachments.php
 * Responsibility: Moves existing attachment files onto the private disk.
 * What it does:
 * - Copies every attachment that still lives on another disk (the old public
 *   one) to the private disk, verifies the copy, updates disk/visibility and
 *   only then deletes the source file; emptied source directories are pruned.
 * - Skips rows already on the target disk or with a missing source file, so
 *   it can be re-run safely; --dry-run only lists what would move.
 * How to use: `php artisan attachments:privatize --dry-run`, then without it.
 * How to extend: pass --disk=... to target another private disk (e.g. s3).
 */

namespace App\Console\Commands;

use App\Models\Attachment;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class PrivatizeAttachments extends Command
{
    protected $signature = 'attachments:privatize
        {--disk= : Target disk (defaults to the configured Curator disk)}
        {--dry-run : Only list the files that would move}';

    protected $description = 'Move attachment files from the public disk to the private disk';

    public function handle(): int
    {
        $target = (string) ($this->option('disk') ?: config('curator.default_disk'));
        $visibility = (string) config('curator.default_visibility', 'private');
        $dryRun = (bool) $this->option('dry-run');

        if (! config("filesystems.disks.{$target}")) {
            $this->error("Disk [{$target}] is not configured.");

            return self::FAILURE;
        }

        $moved = 0;
        $skipped = 0;

        Attachment::query()->orderBy('id')->chunkById(100, function (Collection $attachments) use ($target, $visibility, $dryRun, &$moved, &$skipped): void {
            foreach ($attachments as $attachment) {
                if ($attachment->disk === $target) {
                    $skipped++;

                    continue;
                }

                $source = Storage::disk($attachment->disk);

                if (blank($attachment->path) || ! $source->exists($attachment->path)) {
                    $this->warn("Skipped attachment {$attachment->getKey()}: file missing on [{$attachment->disk}].");

                    $skipped++;

                    continue;
                }

                if ($dryRun) {
                    $this->line("Would move [{$attachment->disk}] {$attachment->path} to [{$target}].");

                    $moved++;

                    continue;
                }

                $stream = $source->readStream($attachment->path);

                if ($stream === null) {
                    $this->warn("Skipped attachment {$attachment->getKey()}: file could not be read.");

                    $skipped++;

                    continue;
                }

                Storage::disk($target)->writeStream($attachment->path, $stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }

                // Only drop the source once the copy is complete and verified.
                if (! Storage::disk($target)->exists($attachment->path)
                    || Storage::disk($target)->size($attachment->path) !== $source->size($attachment->path)) {
                    $this->error("Copy failed for attachment {$attachment->getKey()}; source kept.");

                    $skipped++;

                    continue;
                }

                $attachment->forceFill(['disk' => $target, 'visibility' => $visibility])->save();

                $source->delete($attachment->path);
                $this->pruneEmptyDirectory($source, $attachment->directory);

                $this->line("Moved attachment {$attachment->getKey()} to [{$target}].");

                $moved++;
            }
        });

        $this->info(($dryRun ? 'Dry run: ' : '')."{$moved} attachment(s) ".($dryRun ? 'to move' : 'moved').", {$skipped} skipped.");

        return self::SUCCESS;
    }

    /**
     * Remove the source directory when the move left it empty; guarded so the
     * disk root can never be deleted.
     */
    private function pruneEmptyDirectory(Filesystem $disk, ?string $directory): void
    {
        if (blank($directory)) {
            return;
        }

        if ($disk->allFiles($directory) === []) {
            $disk->deleteDirectory($directory);
        }
    }
}
