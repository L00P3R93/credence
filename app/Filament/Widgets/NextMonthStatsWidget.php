<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use App\Scopes\ActiveLoanScope;
use App\Services\DashboardStatsService;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class NextMonthStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected ?string $heading = "Next Month Summary";
    protected ?string $pollingInterval = '300s';

    protected function getStats(): array
    {
        $statsService = app(DashboardStatsService::class);
        $stats = $statsService->getNextMonthStats();

        $start = Carbon::now()->startOfYear();
        $end = Carbon::now()->endOfYear();

        $newLoansChart = Trend::query(
            Loan::query()->withGlobalScope('active_loan', new ActiveLoanScope())->where('new_loan', true)
        )
            ->dateColumn('due_date')
            ->between($start, $end)
            ->perMonth()
            ->sum('loan_amount');

        $oldLoansChart = Trend::query(
            Loan::query()->withGlobalScope('active_loan', new ActiveLoanScope())->where('old_loan', true)->where('is_from_top_up', false)
        )
            ->dateColumn('due_date')
            ->between($start, $end)
            ->perMonth()
            ->sum('loan_amount');

        $topUpsChart = Trend::query(
            Loan::query()->withGlobalScope('active_loan', new ActiveLoanScope())->where('is_from_top_up', true)
        )
            ->dateColumn('due_date')
            ->between($start, $end)
            ->perMonth()
            ->count();

        $dueRollChart = Trend::query(
            Loan::query()->withGlobalScope('active_loan', new ActiveLoanScope())->where('due_roll', true)
        )
            ->dateColumn('due_date')
            ->between($start, $end)
            ->perMonth()
            ->sum('loan_amount');

        return [
            Stat::make('New Loans Next Month', 'KES ' . number_format($stats['newLoansNextMonth']))
                ->description('Disbursed new loans')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('danger')
                ->chart($newLoansChart->map(fn (TrendValue $v) => $v->aggregate)->toArray()),

            Stat::make('Old Loans Next Month', 'KES ' . number_format($stats['oldLoansNextMonth']))
                ->description('Disbursed old loans')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success')
                ->chart($oldLoansChart->map(fn (TrendValue $v) => $v->aggregate)->toArray()),

            Stat::make('Refinances Next Month', 'KES ' . number_format($stats['totalTopUpForNextMonth']))
                ->description('Disbursed Refinances')
                ->descriptionIcon('heroicon-o-arrow-up')
                ->color('primary')
                ->chart($topUpsChart->map(fn (TrendValue $v) => $v->aggregate)->toArray()),

            Stat::make('Due Roll Next Month', 'KES ' . number_format($stats['dueRollForNextMonth']))
                ->description('Loans due for rollover')
                ->descriptionIcon(Heroicon::OutlinedArrowUpOnSquareStack)
                ->color('gray')
                ->chart($dueRollChart->map(fn (TrendValue $v) => $v->aggregate)->toArray()),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
