<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    /**
     * Seed the virtual customer/operator selects (see CompanyForm).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['customers'] = $this->record->customers()->pluck('users.id')->all();
        $data['operators'] = $this->record->operators()->pluck('users.id')->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncLinkedUsers('customers', $this->data['customers'] ?? []);
        $this->record->syncLinkedUsers('operators', $this->data['operators'] ?? []);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }
}
