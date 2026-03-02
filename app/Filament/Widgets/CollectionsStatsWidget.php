<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CollectionsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Collections Summary';
    protected function getStats(): array
    {
        $statsService = app(DashboardStatsService::class);
        $stats = $statsService->getCollectionsStats();

        return [
            Stat::make('Collected This Month', 'KES ' . number_format($stats['collectedThisMonth'], 2))
                ->description('Total collections this month')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success')
                ->chart([20, 25, 22, 28, 24, 30, 26]),

            Stat::make('Collected Next Month', 'KES ' . number_format($stats['collectedNextMonth'], 2))
                ->description('Projected collections next month')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('info')
                ->chart([22, 27, 24, 30, 26, 32, 28]),
        ];
    }

    protected function getColumns(): int
    {
        return 2;
    }
}
