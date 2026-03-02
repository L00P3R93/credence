<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LoanOperationsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Current Month LoanBook Breakdown';
    protected function getStats(): array
    {
        $statsService = app(DashboardStatsService::class);
        $stats = $statsService->getCurrentMonthStats();

        return [
            Stat::make('New Loans', 'KES ' . number_format($stats['newLoansThisMonth'], 2))
                ->description('New loans this month')
                ->descriptionIcon('heroicon-o-plus-circle')
                ->color('info')
                ->chart([12, 15, 18, 14, 20, 16, 22]),

            Stat::make('Top-Ups', $stats['totalTopUpThisMonth'])
                ->description('Number of top-ups')
                ->descriptionIcon('heroicon-o-arrow-up-circle')
                ->color('success')
                ->chart([3, 5, 4, 6, 7, 5, 8]),

            Stat::make('Topped Amounts', 'KES ' . number_format($stats['toppedAmount'], 2))
                ->description('Total topped up amounts')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('warning')
                ->chart([8, 12, 10, 14, 11, 15, 13]),

            Stat::make('Amount Rolled', 'KES ' . number_format($stats['totalRolledThisMonth'], 2))
                ->description('Total rolled over amounts')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color('purple')
                ->chart([6, 8, 7, 9, 8, 10, 9]),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
