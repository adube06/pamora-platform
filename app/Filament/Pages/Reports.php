<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PlatformStatsWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Reports extends Page
{
    protected string $view = 'filament.pages.reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected function getHeaderWidgets(): array
    {
        return [
            PlatformStatsWidget::class,
        ];
    }
}
