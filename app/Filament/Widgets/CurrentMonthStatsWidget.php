<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Loan;
use App\Scopes\ActiveLoanScope;
use App\Services\DashboardStatsService;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CurrentMonthStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Current Month Summary';
    protected ?string $pollingInterval = '300s';

    protected function getStats(): array
    {
        $statsService = app(DashboardStatsService::class);
        $stats = $statsService->getCurrentMonthStats();

        $start = Carbon::now()->startOfYear();
        $end = Carbon::now()->endOfYear();

        $activeClientsChart = Trend::query(
            Customer::query()->where('status', 'active')
        )
            ->between($start, $end)
            ->perMonth()
            ->count();

        $loanBookChart = Trend::query(
            Loan::query()->withGlobalScope('active_loan', new ActiveLoanScope())->whereIn('top_up', [0, 2])
        )
            ->dateColumn('due_date')
            ->between($start, $end)
            ->perMonth()
            ->sum('loan_amount');

        $interestChart = Trend::query(
            Loan::query()->withGlobalScope('active_loan', new ActiveLoanScope())->whereIn('top_up', [0, 2])
        )
            ->dateColumn('due_date')
            ->between($start, $end)
            ->perMonth()
            ->sum('loan_interest');

        $salesChart = Trend::query(
            Loan::query()->withGlobalScope('active_loan', new ActiveLoanScope())
        )
            ->dateColumn('given_date')
            ->between($start, $end)
            ->perMonth()
            ->sum('loan_amount');

        return [
            Stat::make('Active Clients', $stats['activeCustomers'])
                ->description('Total active customers')
                ->descriptionIcon('heroicon-o-users')
                ->color('success')
                ->chart($activeClientsChart->map(fn (TrendValue $v) => $v->aggregate)->toArray()),

            Stat::make('Loan Book', 'KES ' . number_format($stats['monthlyLoanBookTotal']))
                ->description('Total loan book this month')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('warning')
                ->chart($loanBookChart->map(fn (TrendValue $v) => $v->aggregate)->toArray()),

            Stat::make('Total Interest', 'KES ' . number_format($stats['monthlyLoanInterest']))
                ->description('Interest earned this month')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('danger')
                ->chart($interestChart->map(fn (TrendValue $v) => $v->aggregate)->toArray()),

            Stat::make('Sales Today', 'KES ' . number_format($stats['todaySales']))
                ->description('Today\'s loan sales')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('primary')
                ->chart($salesChart->map(fn (TrendValue $v) => $v->aggregate)->toArray()),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
