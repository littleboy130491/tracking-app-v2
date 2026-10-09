<?php

/**
 * File: app/Livewire/Customer/Dashboard.php
 * Responsibility: Customer portal home: greeting, Type filter, filters and shipment list.
 * What it does:
 * - Lists the shipments of the companies the signed-in user manages in one
 *   combined list; the **Type** filter defaults to All and can narrow to
 *   Export or Import only.
 * - Filters by type, company, B/L, DO or AJU number, status, year and month
 *   (spec.md); the year options come from YearOptions, so the query works on
 *   any database, not only SQLite.
 * - Combines the export and import tables (separate tables) with one UNION
 *   over id/created_at, ordered and paginated in SQL; only the current page's
 *   models are hydrated, with the relations the list and the timeline need.
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
use App\Services\YearOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

        $shipments = $this->paginatedShipments($models, $viewAll, $companyIds);

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
                ->flatMap(fn (string $model): array => YearOptions::forQuery(
                    $model::query()->when(! $viewAll, fn (Builder $query) => $query->whereIn('company_id', $companyIds)),
                ))
                // flatMap drops the year => year keys, so rebuild them.
                ->mapWithKeys(fn (string $year): array => [$year => $year])
                ->sortKeysDesc()
                ->all(),
            'months' => [
                '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
            ],
        ]);
    }

    /**
     * The combined, filtered list, ordered and paginated by the database: one
     * UNION over id/created_at keeps memory to the current page.
     *
     * @param  list<class-string<ExportShipment|ImportShipment>>  $models
     * @param  list<int>  $companyIds
     * @return LengthAwarePaginator<int, ExportShipment|ImportShipment>
     */
    private function paginatedShipments(array $models, bool $viewAll, array $companyIds): LengthAwarePaginator
    {
        $union = null;

        foreach ($models as $model) {
            $member = $this->shipmentQuery($model, $viewAll, $companyIds)
                ->select(['id', 'created_at'])
                ->selectRaw('? as model_class', [$model]);

            $union = $union === null ? $member : $union->unionAll($member);
        }

        $perPage = in_array($this->perPage, [15, 25, 50, 100], true) ? $this->perPage : 50;
        $page = max(1, (int) $this->getPage());

        $paginator = DB::query()
            ->fromSub($union, 'shipments')
            ->orderByDesc('created_at')
            // Stable tie-breaker so equally dated rows never swap pages.
            ->orderBy('model_class')
            ->orderByDesc('id')
            ->paginate($perPage, page: $page);

        $paginator->withPath(request()->url());
        $paginator->setCollection($this->hydratePage($paginator->getCollection()));

        return $paginator;
    }

    /**
     * Load the page's rows as models with the relations the list and the
     * timeline read, so no column costs a query per row.
     *
     * @param  Collection<int, object{model_class: string, id: int}>  $rows
     * @return Collection<int, ExportShipment|ImportShipment>
     */
    private function hydratePage(Collection $rows): Collection
    {
        $models = collect();

        foreach ($rows->groupBy('model_class') as $modelClass => $modelRows) {
            $query = $modelClass::query()->with([
                'company',
                'activityLogs' => fn ($query) => $query->where('event', 'milestone_changed'),
            ]);

            if ($modelClass === ImportShipment::class) {
                $query->with('hsCodes');
            }

            $models = $models->merge(
                $query->whereIn('id', $modelRows->pluck('id'))->get()
                    ->keyBy(fn (Model $shipment): string => $shipment::class.':'.$shipment->getKey()),
            );
        }

        return $rows
            ->map(fn (object $row): ?Model => $models->get($row->model_class.':'.$row->id))
            ->filter()
            ->values();
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
            ->when($this->company !== '', fn (Builder $query) => $query->where('company_id', (int) $this->company))
            ->when($this->number !== '', fn (Builder $query) => $query->where(
                fn (Builder $query) => $query
                    ->where('bl_number', 'like', '%'.$this->number.'%')
                    ->orWhere('aju_number', 'like', '%'.$this->number.'%')
                    // DO number exists on export shipments only.
                    ->when($model === ExportShipment::class, fn (Builder $query) => $query->orWhere('do_number', 'like', '%'.$this->number.'%')),
            ))
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->year !== '', fn (Builder $query) => $query->whereYear('created_at', (int) $this->year))
            ->when($this->month !== '', fn (Builder $query) => $query->whereMonth('created_at', (int) $this->month));
    }
}
