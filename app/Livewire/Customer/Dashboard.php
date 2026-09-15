<?php

/**
 * File: app/Livewire/Customer/Dashboard.php
 * Responsibility: Customer portal home: greeting, filters and shipment list.
 * What it does:
 * - Lists the bills of lading of the companies the signed-in user manages,
 *   filtered by company, number, status, year and month (spec.md).
 * - Scoping is enforced in the query, so a customer can never see another
 *   company's shipments, even by guessing an id or tampering with the filter.
 * How to use: full-page Livewire component on route customer.dashboard.
 * How to extend: add more filters as public properties with matching ->when().
 */

namespace App\Livewire\Customer;

use App\Enums\BillOfLadingStatus;
use App\Models\BillOfLading;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.portal')]
class Dashboard extends Component
{
    use WithPagination;

    public string $company = '';

    public string $number = '';

    public string $status = '';

    public string $year = '';

    public string $month = '';

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
        $companyIds = auth()->user()->companies()->pluck('companies.id')->all();

        $billOfLadings = BillOfLading::query()
            ->whereIn('company_id', $companyIds)
            ->with(['company', 'containers'])
            ->when($this->company !== '', fn (Builder $query) => $query->where('company_id', (int) $this->company))
            ->when($this->number !== '', fn (Builder $query) => $query->where(
                fn (Builder $inner) => $inner
                    ->where('bl_number', 'like', '%'.$this->number.'%')
                    ->orWhere('reference_number', 'like', '%'.$this->number.'%'),
            ))
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->year !== '', fn (Builder $query) => $query->whereYear('created_at', (int) $this->year))
            ->when($this->month !== '', fn (Builder $query) => $query->whereMonth('created_at', (int) $this->month))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.customer.dashboard', [
            'billOfLadings' => $billOfLadings,
            // Only the companies this user manages may appear as filter options.
            'companies' => auth()->user()->companies()->orderBy('name')->pluck('name', 'companies.id')->all(),
            'statuses' => BillOfLadingStatus::options(),
            'years' => BillOfLading::query()
                ->whereIn('company_id', $companyIds)
                ->selectRaw('distinct strftime("%Y", created_at) as year')
                ->orderByDesc('year')
                ->pluck('year', 'year')
                ->filter()
                ->all(),
            'months' => [
                '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
            ],
        ]);
    }
}
