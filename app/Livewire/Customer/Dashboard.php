<?php

/**
 * File: app/Livewire/Customer/Dashboard.php
 * Responsibility: Customer portal home: greeting, Type filter, filters and shipment list.
 * What it does:
 * - Lists the shipments of the companies the signed-in user manages in one
 *   combined list; the **Type** filter defaults to All and can narrow to
 *   Export or Import only.
 * - Filters by type, company, number (B/L, container, seal), status, year and
 *   month (spec.md).
 * - Merges the export and import tables in PHP (they are separate tables) and
 *   paginates the combined result manually.
 * - Adds each shipment's latest reached milestone (Latest Event) and its
 *   reached sailing fields (POD / Vessel arrival), both from
 *   ShipmentTimeline::forShipment() so admin milestone gating applies.
 * - Privileged staff (admin/super_admin) see every shipment; customers stay
 *   scoped to their assigned companies.
 * How to use: full-page Livewire component on route customer.dashboard.
 * How to extend: add more filters as public properties with matching ->when().
 */

namespace App\Livewire\Customer;

use App\Enums\ShipmentStatus;
use App\Models\Company;
use App\Models\ExportShipment;
use App\Models\ImportShipment;
use App\Services\ShipmentTimeline;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.portal')]
class Dashboard extends Component
{
    use WithPagination;

    /** Type filter: '' (All), 'export' or 'import'. */
    public string $type = '';

    public string $company = '';

    public string $number = '';

    public string $status = '';

    public string $year = '';

    public string $month = '';

    /** Rows per page; the view offers a short list of page sizes. */
    public int $perPage = 50;

    public function updated(string $property): void
    {
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['company', 'number', 'status', 'year', 'month']);
        $this->resetPage();
    }

    public function render(): View
    {
        $user = auth()->user();
        $viewAll = $user->canViewAllShipments();
        $companyIds = $viewAll ? [] : $user->companies()->pluck('companies.id')->all();

        $models = match ($this->type) {
            'export' => [ExportShipment::class],
            'import' => [ImportShipment::class],
            default => [ExportShipment::class, ImportShipment::class],
        };

        // Export and Import live in separate tables, so the combined list is
        // merged in PHP and paginated manually instead of via paginate().
        $rows = collect();
        foreach ($models as $model) {
            $rows = $rows->merge($this->shipmentQuery($model, $viewAll, $companyIds)->get());
        }

        $rows = $rows->sortByDesc('created_at')->values();

        $perPage = in_array($this->perPage, [15, 25, 50, 100], true) ? $this->perPage : 50;
        $page = max(1, (int) $this->getPage());
        $total = $rows->count();
        $shipments = new LengthAwarePaginator(
            $rows->forPage($page, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()],
        );
        $shipments->withPath(request()->url());

        // One forShipment() call per row feeds both the Latest event column
        // and the POD / Vessel arrival column, so milestone gating matches the
        // detail pages exactly. Keys carry the class name because the two
        // shipment tables have overlapping ids.
        $timeline = app(ShipmentTimeline::class);
        $latest = collect();
        $sailing = collect();
        foreach ($shipments->getCollection() as $shipment) {
            $entries = $timeline->forShipment($shipment);
            $key = $shipment::class.':'.$shipment->getKey();
            $latest[$key] = $timeline->latestReached($entries);
            $sailing[$key] = $timeline->sailingInformation($entries);
        }

        return view('livewire.customer.dashboard', [
            'shipments' => $shipments,
            'latest' => $latest,
            'sailing' => $sailing,
            // Each row links to its own process detail page; the view resolves
            // the route name/param from the shipment instance.
            'companies' => $viewAll
                ? Company::query()->orderBy('name')->pluck('name', 'id')->all()
                : $user->companies()->orderBy('name')->pluck('name', 'companies.id')->all(),
            // Drafts never reach the portal (visibleInPortal), so the filter
            // only offers the customer-visible states.
            'statuses' => collect(ShipmentStatus::options())
                ->except(ShipmentStatus::Draft->value)
                ->all(),
            'years' => collect($models)
                ->flatMap(fn (string $model) => $model::query()
                    ->when(! $viewAll, fn (Builder $query) => $query->whereIn('company_id', $companyIds))
                    ->selectRaw('distinct strftime("%Y", created_at) as year')
                    ->pluck('year'))
                ->filter()
                ->unique()
                ->sortDesc()
                ->values()
                ->mapWithKeys(fn (string $year) => [$year => $year])
                ->all(),
            'months' => [
                '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
            ],
        ]);
    }

    /**
     * The shared filter query for one shipment model (export or import).
     *
     * @param  class-string<ExportShipment|ImportShipment>  $model
     * @param  list<int>  $companyIds
     * @return Builder<ExportShipment|ImportShipment>
     */
    private function shipmentQuery(string $model, bool $viewAll, array $companyIds): Builder
    {
        return $model::query()
            ->visibleInPortal()
            ->when(! $viewAll, fn (Builder $query) => $query->whereIn('company_id', $companyIds))
            ->with(['company', 'containers'])
            ->when($this->company !== '', fn (Builder $query) => $query->where('company_id', (int) $this->company))
            ->when($this->number !== '', fn (Builder $query) => $query->where(
                fn (Builder $inner) => $inner
                    ->where('bl_number', 'like', '%'.$this->number.'%')
                    ->orWhereHas('containers', fn (Builder $containers) => $containers
                        ->where('container_number', 'like', '%'.$this->number.'%')
                        // Only export containers carry a seal number.
                        ->when($model === ExportShipment::class, fn (Builder $seal) => $seal->orWhere('seal_number', 'like', '%'.$this->number.'%'))),
            ))
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->year !== '', fn (Builder $query) => $query->whereYear('created_at', (int) $this->year))
            ->when($this->month !== '', fn (Builder $query) => $query->whereMonth('created_at', (int) $this->month))
            ->orderByDesc('created_at');
    }
}
