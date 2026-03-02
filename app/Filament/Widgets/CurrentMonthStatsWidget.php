<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CurrentMonthStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Current Month Summary';

    protected function getStats(): array
    {
        $statsService = app(DashboardStatsService::class);
        $stats = $statsService->getCurrentMonthStats();

        return [
            Stat::make('Active Clients', $stats['activeCustomers'])
                ->description('Total active customers')
                ->descriptionIcon('heroicon-o-users')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Loan Book', 'KES ' . number_format($stats['monthlyLoanBookTotal'], 2))
                ->description('Total loan book this month')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('warning')
                ->chart([10, 15, 12, 18, 14, 20, 16]),

            Stat::make('Total Interest', 'KES ' . number_format($stats['monthlyLoanInterest'], 2))
                ->description('Interest earned this month')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('danger')
                ->chart([5, 8, 6, 9, 7, 10, 8]),

            Stat::make('Sales Today', 'KES ' . number_format($stats['todaySales'], 2))
                ->description('Today\'s loan sales')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('primary')
                ->chart([3, 5, 4, 6, 5, 7, 6]),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
