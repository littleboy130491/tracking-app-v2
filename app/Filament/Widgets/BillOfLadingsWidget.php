<?php

/**
 * File: app/Filament/Widgets/BillOfLadingsWidget.php
 * Responsibility: Dashboard shortcuts into the two Bill of Ladings lists.
 * What it does:
 * - Renders one card per shipment list (Export, Import) linking straight to
 *   that resource's index page, so the dashboard opens into the daily work.
 * How to use: discovered automatically by the admin panel (see
 *   AdminPanelProvider::discoverWidgets()).
 * How to extend: add a card in the widget's blade view.
 */

namespace App\Filament\Widgets;

use App\Filament\Resources\ExportShipments\ExportShipmentResource;
use App\Filament\Resources\ImportShipments\ImportShipmentResource;
use Filament\Widgets\Widget;

class BillOfLadingsWidget extends Widget
{
    protected static ?int $sort = -2;

    protected static bool $isLazy = false;

    /**
     * @var view-string
     */
    protected string $view = 'filament.widgets.bill-of-ladings';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, string>
     */
    protected function getViewData(): array
    {
        return [
            'exportUrl' => ExportShipmentResource::getUrl('index'),
            'importUrl' => ImportShipmentResource::getUrl('index'),
        ];
    }
}
