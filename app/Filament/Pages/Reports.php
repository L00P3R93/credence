<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Reports\ConversionRateReport;
use App\Filament\Pages\Reports\DailySalesReport;
use App\Filament\Pages\Reports\DisbursementReport;
use App\Filament\Pages\Reports\LoanBookReport;
use App\Filament\Pages\Reports\MonthlyCollectionsReport;
use App\Filament\Pages\Reports\MonthlySalesReport;
use App\Filament\Pages\Reports\NewClientsReport;
use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class Reports extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static string | BackedEnum | null $navigationIcon = 'hugeicons-chart-histogram';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Reports';

    protected string $view = 'filament.pages.reports';

    public function getReportGroups(): array
    {
        return [
            [
                'label' => 'General Reports',
                'icon' => 'hugeicons-folder-01',
                'reports' => [
                    [
                        'label' => 'Loan Book Report',
                        'description' => 'All active loans on the book for a given month including new, old, refinanced and rolled.',
                        'icon' => 'hugeicons-book-01',
                        'url' => LoanBookReport::getUrl(),
                    ],
                    [
                        'label' => 'Disbursement Report',
                        'description' => 'All loans disbursed in a given month with totals by loan type.',
                        'icon' => 'hugeicons-money-send-02',
                        'url' => DisbursementReport::getUrl(),
                    ],
                    [
                        'label' => 'New Clients Report',
                        'description' => 'All first-time loan clients onboarded in a given month.',
                        'icon' => 'hugeicons-user-add-01',
                        'url' => NewClientsReport::getUrl(),
                    ],
                    [
                        'label' => 'Conversion Rate Report',
                        'description' => 'Lead-to-customer conversion metrics per sales agent, filterable by month and year.',
                        'icon' => 'hugeicons-chart-line-data-01',
                        'url' => ConversionRateReport::getUrl(),
                    ],
                ],
            ],
            [
                'label' => 'Sales Reports',
                'icon' => 'hugeicons-chart-bar-line',
                'reports' => [
                    [
                        'label' => 'Monthly Sales Report',
                        'description' => 'All loans disbursed in a selected month with agent and type breakdown.',
                        'icon' => 'hugeicons-calendar-03',
                        'url' => MonthlySalesReport::getUrl(),
                    ],
                    [
                        'label' => 'Daily Sales Report',
                        'description' => 'All loans disbursed on a specific date.',
                        'icon' => 'hugeicons-calendar-02',
                        'url' => DailySalesReport::getUrl(),
                    ],
                ],
            ],
            [
                'label' => 'Collections & Recoveries',
                'icon' => 'hugeicons-money-receive-square',
                'reports' => [
                    [
                        'label' => 'Monthly Collections Report',
                        'description' => 'Loan book for any month with principal/interest payment breakdown and collection summary.',
                        'icon' => 'hugeicons-money-receive-02',
                        'url' => MonthlyCollectionsReport::getUrl(),
                    ],
                ],
            ],
        ];
    }
}
