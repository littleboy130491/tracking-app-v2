<?php

/**
 * File: app/Services/ShipmentTimeline.php
 * Responsibility: Builds the customer-visible progress for a shipment and its containers.
 * What it does:
 * - `forShipment()` maps the B/L's milestone sequence to ordered steps, with
 *   their logged datetime and populated shipment-level fields grouped under
 *   the matching step; the first reached step falls back to the document
 *   date, the current step is flagged latest and upcoming steps are pending.
 * - `forContainers()` maps each container onto the B/L's container milestones
 *   as step-by-step progress with a summary tile list and a derived status.
 * - `latestReached()` picks the last reached step off a `forShipment()` list
 *   and `sailingInformation()` lifts the sailing fields from reached steps —
 *   both reuse the same milestone locking as the timeline.
 * How to use: `app(ShipmentTimeline::class)->forShipment($shipment)` or
 *   `->forContainers($shipment, $containers)` in the portal.
 * How to extend: add shipment values to `fieldsForMilestone()` and container
 *   values to `containerFieldsForStep()` when a form adds fields to a step;
 *   extend `sailingInformation()`'s label list for new sailing fields.
 */

namespace App\Services;

use App\Enums\ContainerStatus;
use App\Enums\ExportMilestone;
use App\Enums\ImportMilestone;
use App\Models\ActivityLog;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use BackedEnum;
use Carbon\CarbonInterface;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ShipmentTimeline
{
    /**
     * The shipment-level progress: one row per milestone step, in order.
     * Reached steps carry the datetime they were logged at (the first step
     * falls back to the document date); upcoming steps are pending.
     *
     * @return list<ShipmentTimelineEntry>
     */
    public function forShipment(ExportShipment|ImportShipment $shipment): array
    {
        $sequence = $shipment->milestoneSequence();
        $position = $shipment->milestonePosition();
        $reachedAt = $this->milestoneReachedAt($shipment);

        $entries = [];

        foreach ($sequence as $index => $milestone) {
            $step = $index + 1;
            $pending = $step > $position;
            $at = $pending ? null : $reachedAt->get((string) $milestone->value);

            if ($at === null && ! $pending && $index === 0) {
                $at = $shipment->document_received_date ?? $shipment->created_at;
            }

            $entries[] = new ShipmentTimelineEntry(
                title: $milestone instanceof HasLabel ? $milestone->getLabel() : Str::headline((string) $milestone->value),
                occurredAt: $at ? $this->formatAt($at) : null,
                isLatest: ! $pending && $step === $position,
                isPending: $pending,
                fields: $pending ? [] : $this->fieldsForMilestone($shipment, $milestone),
            );
        }

        return $entries;
    }

    /**
     * Per-container portal progress keyed by container id: a summary tile list
     * plus the B/L's container milestones as step-by-step entries. Containers
     * have no milestone of their own — they follow the B/L's position, the
     * same rule the admin forms use to unlock container fields. One
     * milestone-log query serves every container.
     *
     * @param  iterable<ExportContainer|ImportContainer>  $containers
     * @return array<int, ContainerProgress>
     */
    public function forContainers(ExportShipment|ImportShipment $shipment, iterable $containers): array
    {
        $sequence = $shipment->milestoneSequence();
        $position = $shipment->milestonePosition();
        $complete = $position === count($sequence);
        $reachedAt = $this->milestoneReachedAt($shipment);

        $progress = [];

        foreach ($containers as $container) {
            $steps = [];

            foreach ($sequence as $index => $milestone) {
                if (! $milestone->isContainerStep()) {
                    continue;
                }

                $step = $index + 1;
                $pending = $step > $position;
                $at = $pending ? null : $reachedAt->get((string) $milestone->value);

                $steps[] = new ShipmentTimelineEntry(
                    title: $milestone instanceof HasLabel ? $milestone->getLabel() : Str::headline((string) $milestone->value),
                    occurredAt: $at ? $this->formatAt($at) : null,
                    isLatest: ! $complete && $step === $position,
                    isPending: $pending,
                    fields: $pending ? [] : $this->containerFieldsForStep($container, $milestone),
                );
            }

            $progress[$container->getKey()] = new ContainerProgress(
                summary: $this->containerSummaryFields($container, $sequence, $position),
                steps: $steps,
                status: $this->derivedContainerStatus($container, $complete, $steps),
                trackingUrl: $this->trackingUrl($container),
                chips: $this->containerChips($container, $sequence, $position),
            );
        }

        return $progress;
    }

    /**
     * The last reached step of a `forShipment()` list — the shipment's current
     * position. Powers list rows like the dashboard's Latest Event column.
     *
     * @param  list<ShipmentTimelineEntry>  $entries
     */
    public function latestReached(array $entries): ?ShipmentTimelineEntry
    {
        $latest = null;

        foreach ($entries as $entry) {
            if (! $entry->isPending) {
                $latest = $entry;
            }
        }

        return $latest;
    }

    /**
     * The sailing facts for the portal card, read off the reached steps'
     * fields so the same milestone locking applies — a value whose unlocking
     * milestone is not yet reached never surfaces. First occurrence wins.
     *
     * @param  list<ShipmentTimelineEntry>  $entries
     * @return array<string, string>
     */
    public function sailingInformation(array $entries): array
    {
        $labels = [
            'Port of loading',
            'Departure date',
            'Port of discharge',
            'Arrival time / ETA',
            'Vessel name',
            'Voyage number',
            'Shipping line',
        ];

        $found = [];

        foreach ($entries as $entry) {
            if ($entry->isPending) {
                continue;
            }

            foreach ($entry->fields as $field) {
                if (in_array($field['label'], $labels, true) && ! isset($found[$field['label']])) {
                    $found[$field['label']] = $field['value'];
                }
            }
        }

        $sailing = [];

        foreach ($labels as $label) {
            if (isset($found[$label])) {
                $sailing[$label] = $found[$label];
            }
        }

        return $sailing;
    }

    /**
     * Whether the sailing strip has a route to draw. Vessel facts alone do
     * not count — they live in the shipment overview — so a shipment with
     * only a vessel or line (or nothing) renders no strip at all instead of
     * an empty box.
     *
     * @param  array<string, string>  $sailing
     */
    public static function hasRoute(array $sailing): bool
    {
        return filled($sailing['Port of loading'] ?? null)
            || filled($sailing['Departure date'] ?? null)
            || filled($sailing['Port of discharge'] ?? null)
            || filled($sailing['Arrival time / ETA'] ?? null);
    }

    /**
     * When each milestone was reached, from the milestone-change log trail.
     * A revisited step keeps its most recent pass.
     *
     * @return Collection<string, CarbonInterface>
     */
    private function milestoneReachedAt(ExportShipment|ImportShipment $shipment): Collection
    {
        return ActivityLog::query()
            ->where($shipment->activityLogShipmentKey(), $shipment->getKey())
            ->where('event', 'milestone_changed')
            ->orderBy('occurred_at')
            ->get()
            ->mapWithKeys(fn (ActivityLog $log) => [
                (string) ($log->new_values['milestone'] ?? '') => $log->occurred_at,
            ]);
    }

    /**
     * The collapsed-row weight chips — each one only appears once the
     * B/L milestone that unlocks the field in the admin form has been
     * reached, so a filled-but-locked value never leaks into the row.
     *
     * @param  list<BackedEnum>  $sequence
     * @return list<string>
     */
    private function containerChips(ExportContainer|ImportContainer $container, array $sequence, int $position): array
    {
        if ($container instanceof ExportContainer) {
            return array_values(array_filter([
                $this->milestoneReached($sequence, $position, ExportMilestone::GateInCy) && filled($container->vgm_value)
                    ? 'VGM '.$container->vgm_value.' kg'
                    : null,
            ]));
        }

        return array_values(array_filter([
            $this->milestoneReached($sequence, $position, ImportMilestone::ResponseBilling) && filled($container->gross_weight)
                ? 'Gross weight '.$container->gross_weight.' kg'
                : null,
        ]));
    }

    /**
     * Whether a milestone sits inside the shipment's sequence and has been
     * reached (used for row chips and summary tiles, whose unlocking
     * milestones are not container steps themselves).
     *
     * @param  list<BackedEnum>  $sequence
     */
    private function milestoneReached(array $sequence, int $position, BackedEnum $milestone): bool
    {
        $index = array_search($milestone, $sequence, true);

        return $index !== false && $index + 1 <= $position;
    }

    /**
     * The container's portal status, derived from the B/L's milestone because
     * containers have no milestone of their own. Only import containers carry
     * an editable status in the admin, so an explicit cancellation can only
     * come from there — it always wins.
     *
     * @param  list<ShipmentTimelineEntry>  $steps
     */
    private function derivedContainerStatus(
        ExportContainer|ImportContainer $container,
        bool $complete,
        array $steps,
    ): ContainerStatus {
        if ($container instanceof ImportContainer && $container->status === ContainerStatus::Cancelled) {
            return ContainerStatus::Cancelled;
        }

        if ($complete) {
            return ContainerStatus::Completed;
        }

        $reached = array_filter($steps, fn (ShipmentTimelineEntry $step): bool => ! $step->isPending);

        return $reached === [] ? ContainerStatus::Pending : ContainerStatus::InProgress;
    }

    /**
     * The live-tracking link, only when it is a real web URL — anything else
     * (e.g. javascript:) renders as plain text and never as a link.
     */
    private function trackingUrl(ExportContainer|ImportContainer $container): ?string
    {
        $url = $container->tracking_position_url;

        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)
            ? $url
            : null;
    }

    /**
     * The tracking-URL row for a step: a link when the stored value is a web
     * URL, the raw text otherwise — a non-http(s) value never becomes an href.
     *
     * @return array{label: string, value: ?string, href?: string}
     */
    private function trackingUrlField(mixed $url): array
    {
        if (is_string($url) && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)) {
            return ['label' => 'Tracking position (url)', 'value' => 'Open tracking link', 'href' => $url];
        }

        return ['label' => 'Tracking position (url)', 'value' => $url];
    }

    /**
     * Populated container form values, grouped by the B/L milestone that
     * unlocks them in Filament (see the ContainerFields gating on the
     * shipment forms). Labels match the admin forms exactly.
     *
     * @return list<array{label: string, value: string, href?: string, wide?: bool}>
     */
    private function containerFieldsForStep(
        ExportContainer|ImportContainer $container,
        ExportMilestone|ImportMilestone $milestone,
    ): array {
        if ($container instanceof ExportContainer && $milestone instanceof ExportMilestone) {
            return $this->displayFields(match ($milestone) {
                ExportMilestone::PickupEmptyContainer => [
                    ['label' => 'Driver name', 'value' => $container->driver_name],
                    ['label' => 'Vehicle / Truck Number', 'value' => $container->license_number],
                    ['label' => 'Driver License Number', 'value' => $container->driver_license_number],
                ],
                ExportMilestone::OnTheWayToFactory => [
                    ['label' => 'Tracking position', 'value' => $container->tracking_position],
                    $this->trackingUrlField($container->tracking_position_url),
                ],
                ExportMilestone::StuffingPebNpe => [
                    ['label' => 'Stuffing status at Factory', 'value' => $container->stuffing_status],
                ],
                ExportMilestone::CheckingPebNpe => [
                    ['label' => 'Port of loading', 'value' => $container->port_of_loading],
                    ['label' => 'Gate in CY at', 'value' => $container->gate_in_cy_at],
                ],
                ExportMilestone::GateInCy => [
                    ['label' => 'VGM (kg)', 'value' => $container->vgm_value],
                ],
                ExportMilestone::FinalChecking => [
                    ['label' => 'Final checked', 'value' => $container->final_checked],
                    ['label' => 'Final checked at', 'value' => $container->final_checked_at],
                ],
                default => [],
            });
        }

        if ($container instanceof ImportContainer && $milestone instanceof ImportMilestone) {
            return $this->displayFields(match ($milestone) {
                ImportMilestone::GateOutCy => [
                    ['label' => 'Gate out CY', 'value' => $container->gate_out_cy_at],
                    ['label' => 'Driver name', 'value' => $container->driver_name],
                    ['label' => 'No. License', 'value' => $container->license_number],
                ],
                ImportMilestone::OnTheWayToFactory => [
                    ['label' => 'Tracking position driver', 'value' => $container->tracking_position],
                    $this->trackingUrlField($container->tracking_position_url),
                ],
                ImportMilestone::ArrivedAtFactory => [
                    ['label' => 'Loading in factory status', 'value' => $container->factory_loading_status],
                ],
                ImportMilestone::EmptyReturned => [
                    ['label' => 'Return depot name', 'value' => $container->return_depot_name],
                    ['label' => 'Return date', 'value' => $container->empty_returned_at],
                ],
                default => [],
            });
        }

        return [];
    }

    /**
     * The summary tiles shown above the stepper: identity and cargo facts for
     * one container, matching the admin field labels. Every tile is gated on
     * the B/L milestone that unlocks it in the admin form — the same rule as
     * the row chips — so a filled-but-locked value never leaks into the card.
     *
     * @param  list<BackedEnum>  $sequence
     * @return list<array{label: string, value: string, href?: string, wide?: bool}>
     */
    private function containerSummaryFields(ExportContainer|ImportContainer $container, array $sequence, int $position): array
    {
        $size = match ((string) $container->size) {
            '20' => '20 ft',
            '40' => '40 ft',
            '45' => '45 ft',
            default => $container->size,
        };

        if ($container instanceof ExportContainer) {
            if (! $this->milestoneReached($sequence, $position, ExportMilestone::PickupEmptyContainer)) {
                return [];
            }

            return $this->displayFields([
                ['label' => 'Container Size', 'value' => $size],
                ['label' => 'Seal number', 'value' => $container->seal_number],
            ]);
        }

        $fields = [];

        if ($this->milestoneReached($sequence, $position, ImportMilestone::ResponseBilling)) {
            $fields = [
                ['label' => 'Container Size', 'value' => $size],
                ['label' => 'Seal number', 'value' => $container->seal_number],
                ['label' => 'Gross weight (kg)', 'value' => $container->gross_weight],
                ['label' => 'CBM / measurement', 'value' => $container->cbm],
                ['label' => 'Packages', 'value' => $container->packages],
                ['label' => 'HS codes', 'value' => $container->hsCodes->pluck('code')->implode(', ')],
                ['label' => 'Description of goods', 'value' => $container->description_of_goods, 'wide' => true],
            ];
        }

        if ($this->milestoneReached($sequence, $position, ImportMilestone::EmptyReturned)) {
            $fields[] = ['label' => 'Completed at', 'value' => $container->completed_at];
        }

        return $this->displayFields($fields);
    }

    /**
     * Populated shipment-level form values, grouped by the milestone that
     * unlocks them in Filament. Container fields stay in the portal's container section.
     *
     * @return list<array{label: string, value: string, href?: string, wide?: bool}>
     */
    private function fieldsForMilestone(
        ExportShipment|ImportShipment $shipment,
        ExportMilestone|ImportMilestone $milestone,
    ): array {
        if ($shipment instanceof ExportShipment && $milestone instanceof ExportMilestone) {
            return $this->displayFields(match ($milestone) {
                ExportMilestone::CheckingBookingOrder => [
                    ['label' => 'B/L number', 'value' => $shipment->bl_number],
                    ['label' => 'DO number', 'value' => $shipment->do_number],
                    ['label' => 'Shipping line', 'value' => $shipment->shipping_line],
                    ['label' => 'Vessel name', 'value' => $shipment->vessel_name],
                    ['label' => 'Voyage number', 'value' => $shipment->voyage_number],
                    ['label' => 'Port of loading', 'value' => $shipment->port_of_loading],
                    ['label' => 'Port of discharge', 'value' => $shipment->port_of_discharge],
                    ['label' => 'Closing time at depot', 'value' => $shipment->depot_closing_at],
                    ['label' => 'Closing time at CY', 'value' => $shipment->cy_closing_at],
                    ['label' => 'Shipment mode', 'value' => $shipment->shipment_mode],
                ],
                ExportMilestone::PickupEmptyContainer => [
                    ['label' => 'Pick up depot', 'value' => $shipment->pickup_depot_name],
                    ['label' => 'Stuffing date', 'value' => $shipment->stuffing_date],
                    ['label' => 'Stuffing destination', 'value' => $shipment->stuffing_destination],
                ],
                ExportMilestone::StuffingPebNpe => [
                    ['label' => 'AJU number', 'value' => $shipment->aju_number],
                ],
                ExportMilestone::GateInCy => [
                    ['label' => 'Departure date', 'value' => $shipment->departure_date],
                    ['label' => 'Arrival time / ETA', 'value' => $shipment->eta_at],
                ],
                default => [],
            });
        }

        if ($shipment instanceof ImportShipment && $milestone instanceof ImportMilestone) {
            return $this->displayFields(match ($milestone) {
                ImportMilestone::CheckingDocument => [
                    ['label' => 'B/L number', 'value' => $shipment->bl_number],
                    ['label' => 'Shipment mode', 'value' => $shipment->shipment_mode],
                ],
                ImportMilestone::DraftPib => [
                    ['label' => 'Shipping line', 'value' => $shipment->shipping_line],
                ],
                ImportMilestone::CheckingDraftPib => [
                    ['label' => 'Vessel name', 'value' => $shipment->vessel_name],
                ],
                ImportMilestone::WaitingConfirmation => [
                    ['label' => 'Confirmation checklist', 'value' => $shipment->confirmation_checklist],
                ],
                ImportMilestone::FinalSendingPib => [
                    ['label' => 'AJU number', 'value' => $shipment->aju_number],
                    ['label' => 'Voyage number', 'value' => $shipment->voyage_number],
                    ['label' => 'Status billing', 'value' => $shipment->billing_issuance_status],
                ],
                ImportMilestone::ThcPayment => [
                    ['label' => 'Port of loading', 'value' => $shipment->port_of_loading],
                ],
                ImportMilestone::WaitingReleaseDo => [
                    ['label' => 'Departure date', 'value' => $shipment->departure_date],
                ],
                ImportMilestone::DoRelease => [
                    ['label' => 'Port of discharge', 'value' => $shipment->port_of_discharge],
                ],
                ImportMilestone::BillingPayment => [
                    ['label' => 'Arrival time / ETA', 'value' => $shipment->eta_at],
                ],
                ImportMilestone::ResponseBilling => [
                    ['label' => 'Billing response', 'value' => $shipment->billing_response],
                    ['label' => 'Description of goods', 'value' => $shipment->goods_description],
                    ['label' => 'Packages', 'value' => $shipment->packages],
                    ['label' => 'HS codes', 'value' => $shipment->hsCodes()->orderBy('code')->pluck('code')->implode(', ')],
                ],
                ImportMilestone::ContainerShippingSchedule => [
                    ['label' => 'Terminal name', 'value' => $shipment->terminal_name],
                    ['label' => 'Date of loading', 'value' => $shipment->loading_date],
                    ['label' => 'Loading destination', 'value' => $shipment->loading_destination],
                ],
                default => [],
            });
        }

        return [];
    }

    /**
     * Drop empty form values and convert model casts into customer-facing
     * text. Optional `href` and `wide` presentation hints pass through as-is.
     *
     * @param  list<array{label: string, value: mixed, href?: string, wide?: bool}>  $fields
     * @return list<array{label: string, value: string, href?: string, wide?: bool}>
     */
    private function displayFields(array $fields): array
    {
        $displayFields = [];

        foreach ($fields as $field) {
            $value = $this->formatFieldValue($field['value']);

            if ($value === null) {
                continue;
            }

            $displayFields[] = ['label' => $field['label'], 'value' => $value]
                + array_intersect_key($field, array_flip(['href', 'wide']));
        }

        return $displayFields;
    }

    private function formatFieldValue(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $this->formatAt($value);
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($value instanceof HasLabel) {
            $label = $value->getLabel();

            return is_string($label) ? $label : null;
        }

        if ($value instanceof BackedEnum) {
            if (method_exists($value, 'label')) {
                $label = $value->label();

                return is_string($label) ? $label : null;
            }

            return Str::headline((string) $value->value);
        }

        if (is_string($value) || is_numeric($value)) {
            $text = trim((string) $value);

            return $text === '' ? null : $text;
        }

        return null;
    }

    /**
     * Datetimes keep their time; bare dates (document date) show date only.
     */
    private function formatAt(CarbonInterface $at): string
    {
        return $at->format('H:i') === '00:00' ? $at->format('d M Y') : $at->format('d M Y H:i');
    }
}
