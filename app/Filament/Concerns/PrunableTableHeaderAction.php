<?php

/**
 * File: app/Filament/Concerns/PrunableTableHeaderAction.php
 * Responsibility: Builds the super-admin-only "Prune old data" table header action.
 * What it does:
 * - Confirms first, showing how many rows older than the retention window
 *   (3 years, via OldDataPruner) would be permanently deleted.
 * - Runs OldDataPruner::pruneFor(), reports the count and clears the table cache.
 * How to use: `PrunableTableHeaderAction::make('export-shipments')` inside the
 *   `->headerActions([...])` of a resource table.
 * How to extend: add the key to OldDataPruner::MANIFEST, then call this builder.
 */

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Models\User;
use App\Services\Prune\OldDataPruner;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class PrunableTableHeaderAction
{
    public static function make(string $key): Action
    {
        $pruner = app(OldDataPruner::class);
        $label = OldDataPruner::labelFor($key);

        return Action::make("prune-{$key}")
            ->label('Prune old data')
            ->icon(Heroicon::OutlinedTrash)
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (): bool => (bool) auth()->user()?->canPruneOldData())
            ->modalHeading('Prune old data')
            ->modalDescription(function () use ($pruner, $key, $label): string {
                $count = $pruner->countFor($key);
                $cutoff = $pruner->cutoff()->toFormattedDateString();

                return "Permanently delete {$count} {$label} created before {$cutoff} "
                    .'(older than '.OldDataPruner::RETENTION_YEARS.' years). This cannot be undone.';
            })
            ->modalSubmitActionLabel('Delete permanently')
            ->action(function () use ($pruner, $key, $label): void {
                /** @var User|null $user */
                $user = auth()->user();

                if (! $user?->canPruneOldData()) {
                    Notification::make()
                        ->danger()
                        ->title('Not allowed')
                        ->body('Only super admins may prune old data.')
                        ->send();

                    return;
                }

                $deleted = $pruner->pruneFor($key);

                Notification::make()
                    ->success()
                    ->title('Old data pruned')
                    ->body("Deleted {$deleted} {$label}.")
                    ->send();
            });
    }
}
