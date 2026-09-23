<?php

/**
 * File: app/Services/ContainerProgress.php
 * Responsibility: The portal's per-container view model — summary tiles plus journey steps.
 * What it does:
 * - Carries the summary field tiles and the milestone-driven step list the
 *   container accordion renders, plus the derived status and tracking link.
 * - Exposes the helpers the Blade row needs: current/next step, reach counts
 *   and whether the journey is complete.
 * How to use: built by ShipmentTimeline::forContainers(), read by
 *   livewire/customer/partials/shipment-containers.blade.php.
 * How to extend: add another display property here and fill it in the builder.
 */

namespace App\Services;

use App\Enums\ContainerStatus;

readonly class ContainerProgress
{
    /**
     * @param  list<array{label: string, value: string, href?: string, wide?: bool}>  $summary
     * @param  list<ShipmentTimelineEntry>  $steps  isLatest = current step, isPending = upcoming, else done
     * @param  list<string>  $chips  seal/weight row chips, only for unlocked milestones
     */
    public function __construct(
        public array $summary,
        public array $steps,
        public ContainerStatus $status,
        public ?string $trackingUrl = null,
        public array $chips = [],
    ) {}

    /**
     * The step the container sits on — null when the shipment's current
     * milestone is not a container step or the journey is complete.
     */
    public function current(): ?ShipmentTimelineEntry
    {
        foreach ($this->steps as $step) {
            if ($step->isLatest) {
                return $step;
            }
        }

        return null;
    }

    /**
     * The most recently reached step — the last non-pending entry.
     */
    public function latestReached(): ?ShipmentTimelineEntry
    {
        $latest = null;

        foreach ($this->steps as $step) {
            if (! $step->isPending) {
                $latest = $step;
            }
        }

        return $latest;
    }

    /**
     * Steps already reached — done steps plus the current one.
     */
    public function reachedCount(): int
    {
        return count(array_filter(
            $this->steps,
            fn (ShipmentTimelineEntry $step): bool => ! $step->isPending,
        ));
    }

    public function totalSteps(): int
    {
        return count($this->steps);
    }

    /**
     * Every step reached and none flagged current — the shipment sits on its
     * final milestone.
     */
    public function isComplete(): bool
    {
        return $this->totalSteps() > 0
            && $this->reachedCount() === $this->totalSteps()
            && $this->current() === null;
    }
}
