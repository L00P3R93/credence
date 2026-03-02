<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatsService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NextMonthStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected ?string $heading = "Next Month Summary";

    protected function getStats(): array
    {
        $statsService = app(DashboardStatsService::class);
        $stats = $statsService->getNextMonthStats();

        return [
            Stat::make('New Loans Next Month', 'KES ' . number_format($stats['newLoansNextMonth'], 2))
                ->description('Projected new loans')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('danger')
                ->chart([15, 18, 20, 16, 22, 18, 24]),

            Stat::make('Top-Ups Next Month', 'KES ' . number_format($stats['totalTopUpForNextMonth'], 2))
                ->description('Projected top-ups')
                ->descriptionIcon('heroicon-o-arrow-up')
                ->color('primary')
                ->chart([5, 7, 6, 8, 7, 9, 8]),

            Stat::make('Topped Amounts Next Month', 'KES ' . number_format($stats['toppedAmountForNextMonth'], 2))
                ->description('Projected topped amounts')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success')
                ->chart([10, 14, 12, 16, 13, 17, 15]),

            Stat::make('Due Roll Next Month', 'KES ' . number_format($stats['dueRollForNextMonth'], 2))
                ->description('Loans due for rollover')
                ->descriptionIcon(Heroicon::OutlinedArrowUpOnSquareStack)
                ->color('gray')
                ->chart([8, 10, 9, 11, 10, 12, 11]),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
