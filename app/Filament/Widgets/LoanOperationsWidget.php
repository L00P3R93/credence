<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use App\Scopes\ActiveLoanScope;
use App\Services\DashboardStatsService;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class LoanOperationsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Current Month LoanBook Breakdown';
    protected ?string $pollingInterval = '300s';
    protected function getStats(): array
    {
        $statsService = app(DashboardStatsService::class);
        $stats = $statsService->getCurrentMonthStats();

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

        $rolledChart = Trend::query(
            Loan::query()->withGlobalScope('active_loan', new ActiveLoanScope())->where('rolled', true)
        )
            ->dateColumn('due_date')
            ->between($start, $end)
            ->perMonth()
            ->sum('loan_amount');

        return [
            Stat::make('New Loans', 'KES ' . number_format($stats['newLoansThisMonth']))
                ->description('New loans this month')
                ->descriptionIcon('heroicon-o-plus-circle')
                ->color('info')
                ->chart($newLoansChart->map(fn (TrendValue $v) => $v->aggregate)->toArray()),

            Stat::make('Old Loans', 'KES ' . number_format($stats['oldLoansThisMonth']))
                ->description('Old loans this month')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('warning')
                ->chart($oldLoansChart->map(fn (TrendValue $v) => $v->aggregate)->toArray()),

            Stat::make('Refinances', number_format($stats['totalTopUpThisMonth']))
                ->description('Refinances this month')
                ->descriptionIcon('heroicon-o-arrow-up-circle')
                ->color('success')
                ->chart($topUpsChart->map(fn (TrendValue $v) => $v->aggregate)->toArray()),

            Stat::make('Amount Rolled', 'KES ' . number_format($stats['totalRolledThisMonth']))
                ->description('Total rolled over amounts')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color('purple')
                ->chart($rolledChart->map(fn (TrendValue $v) => $v->aggregate)->toArray()),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
