<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    /**
     * Link the virtual customer/operator selects after the company exists
     * (see CompanyForm and Company::syncLinkedUsers).
     */
    protected function afterCreate(): void
    {
        $this->record->syncLinkedUsers('customers', $this->data['customers'] ?? []);
        $this->record->syncLinkedUsers('operators', $this->data['operators'] ?? []);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction(),
        ];
    }
}
