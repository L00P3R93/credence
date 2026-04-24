<?php

namespace App\Filament\Pages\Reports;

use App\Enums\LoanStatus;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Loans\LoanResource;
use App\Models\Loan;
use App\Scopes\ActiveLoanScope;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MonthlyCollectionsReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Monthly Collections Report';

    protected string $view = 'filament.pages.reports.monthly-collections-report';

    public int $filterMonth;
    public int $filterYear;

    public function mount(): void
    {
        $this->filterMonth = Carbon::now()->month;
        $this->filterYear = Carbon::now()->year;
    }

    public function updatedFilterMonth(): void
    {
        $this->resetTable();
    }

    public function updatedFilterYear(): void
    {
        $this->resetTable();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    private function collectionStatuses(): array
    {
        return [
            LoanStatus::DISBURSED,
            LoanStatus::OVERDUE,
            LoanStatus::PAST_OVERDUE,
            LoanStatus::CLEARED,
            LoanStatus::DUE_ROLL,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Loan::query()
                ->withoutGlobalScope(ActiveLoanScope::class)
                ->whereIn('status', $this->collectionStatuses())
                ->whereMonth('due_date', $this->filterMonth)
                ->whereYear('due_date', $this->filterYear)
                ->withSum('payments', 'amount')
                ->with(['customer', 'bank', 'collectionOfficer'])
            )
            ->defaultSort('due_date', 'asc')
            ->columns([
                TextColumn::make('id')
                    ->label('Loan ID')
                    ->sortable()
                    ->url(fn (Loan $record): string => LoanResource::getUrl('view', ['record' => $record]))
                    ->color('primary')
                    ->weight('bold'),

                TextColumn::make('customer.name')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Loan $record): string => CustomerResource::getUrl('view', ['record' => $record->customer_id]))
                    ->color('primary'),

                TextColumn::make('bank.name')
                    ->label('Bank')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('loan_amount')
                    ->label('Loan Amount')
                    ->numeric(thousandsSeparator: ',')
                    ->sortable(),

                TextColumn::make('loan_interest')
                    ->label('Loan Interest')
                    ->numeric(thousandsSeparator: ',')
                    ->sortable(),

                TextColumn::make('amount_paid')
                    ->label('Amount Paid')
                    ->numeric(thousandsSeparator: ',')
                    ->default(0)
                    ->state(fn (Loan $record): float => $record->payments_sum_amount ?? 0),

                TextColumn::make('interest_paid')
                    ->label('Interest Paid')
                    ->numeric(thousandsSeparator: ',')
                    ->state(fn (Loan $record): float => min(
                        $record->payments_sum_amount ?? 0,
                        (float) $record->loan_interest
                    ))
                    ->color(fn (Loan $record): string => (($record->payments_sum_amount ?? 0) >= (float) $record->loan_interest) ? 'success' : 'warning'),

                TextColumn::make('principal_paid')
                    ->label('Principal Paid')
                    ->numeric(thousandsSeparator: ',')
                    ->state(fn (Loan $record): float => max(
                        0,
                        ($record->payments_sum_amount ?? 0) - (float) $record->loan_interest
                    )),

                TextColumn::make('collectionOfficer.name')
                    ->label('Collection Officer')
                    ->searchable()
                    ->sortable()
                    ->default('—'),
            ])
            ->filters([
                SelectFilter::make('bank_id')
                    ->label('Bank')
                    ->relationship('bank', 'name')
                    ->native(false)
                    ->preload()
                    ->searchable(),

                SelectFilter::make('collection_officer')
                    ->label('Collection Officer')
                    ->relationship('collectionOfficer', 'name')
                    ->native(false)
                    ->preload()
                    ->searchable(),
            ]);
    }

    public function getCollectionsSummary(): array
    {
        $start = Carbon::create($this->filterYear, $this->filterMonth)->startOfMonth();
        $end = Carbon::create($this->filterYear, $this->filterMonth)->endOfMonth();

        $loans = Loan::withoutGlobalScope(ActiveLoanScope::class)
            ->whereIn('status', $this->collectionStatuses())
            ->whereMonth('due_date', $this->filterMonth)
            ->whereYear('due_date', $this->filterYear)
            ->withSum('payments', 'amount')
            ->get(['loan_amount', 'loan_interest', 'loan_total', 'payments_sum_amount']);

        $totalLoanAmount    = 0.0;
        $totalLoanInterest  = 0.0;
        $totalLoanTotal     = 0.0;
        $collectedInterest  = 0.0;
        $collectedPrincipal = 0.0;
        $collectedTotal     = 0.0;

        foreach ($loans as $loan) {
            $paid = (float) ($loan->payments_sum_amount ?? 0);
            $totalLoanAmount   += (float) $loan->loan_amount;
            $totalLoanInterest += (float) $loan->loan_interest;
            $totalLoanTotal    += (float) $loan->loan_total;
            $interestPaid       = min($paid, (float) $loan->loan_interest);
            $principalPaid      = max(0.0, $paid - (float) $loan->loan_interest);
            $collectedInterest  += $interestPaid;
            $collectedPrincipal += $principalPaid;
            $collectedTotal     += $paid;
        }

        $pct = fn (float $collected, float $expected): string =>
            $expected > 0 ? number_format($collected / $expected * 100, 1) . '%' : '—';

        return [
            'loan_amount' => [
                'expected'   => $totalLoanAmount,
                'collected'  => $collectedPrincipal,
                'percentage' => $pct($collectedPrincipal, $totalLoanAmount),
            ],
            'loan_interest' => [
                'expected'   => $totalLoanInterest,
                'collected'  => $collectedInterest,
                'percentage' => $pct($collectedInterest, $totalLoanInterest),
            ],
            'total' => [
                'expected'   => $totalLoanTotal,
                'collected'  => $collectedTotal,
                'percentage' => $pct($collectedTotal, $totalLoanTotal),
            ],
        ];
    }
}
