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

class DisbursementReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Disbursement Report';

    protected string $view = 'filament.pages.reports.disbursement-report';

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

    private function disbursedStatuses(): array
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
                ->whereIn('status', $this->disbursedStatuses())
                ->where('rolled', false)
                ->whereMonth('given_date', $this->filterMonth)
                ->whereYear('given_date', $this->filterYear)
                ->with(['customer', 'bank', 'bankBranch', 'salesAgent'])
            )
            ->defaultSort('given_date', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('Loan ID')
                    ->sortable()
                    ->url(fn (Loan $record): string => LoanResource::getUrl('view', ['record' => $record]))
                    ->color('primary')
                    ->weight('bold'),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Loan $record): string => CustomerResource::getUrl('view', ['record' => $record->customer_id]))
                    ->color('primary'),

                TextColumn::make('bank.name')
                    ->label('Bank')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('bankBranch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('given_date')
                    ->label('Given Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('loan_type')
                    ->label('Type')
                    ->badge()
                    ->state(fn (Loan $record): string => match (true) {
                        (bool) $record->is_from_top_up => 'Refinanced',
                        (bool) $record->new_loan       => 'New Loan',
                        (bool) $record->old_loan       => 'Old Loan',
                        default                        => 'Other',
                    })
                    ->color(fn (Loan $record): string => match (true) {
                        (bool) $record->is_from_top_up => 'info',
                        (bool) $record->new_loan       => 'success',
                        (bool) $record->old_loan       => 'gray',
                        default                        => 'gray',
                    }),

                TextColumn::make('loan_amount')
                    ->label('Loan Amount')
                    ->numeric(thousandsSeparator: ',')
                    ->sortable(),

                TextColumn::make('loan_interest')
                    ->label('Interest')
                    ->numeric(thousandsSeparator: ',')
                    ->sortable(),

                TextColumn::make('loan_total')
                    ->label('Total')
                    ->numeric(thousandsSeparator: ',')
                    ->sortable(),

                TextColumn::make('salesAgent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('bank_id')
                    ->label('Bank')
                    ->relationship('bank', 'name')
                    ->native(false)
                    ->preload()
                    ->searchable(),

                SelectFilter::make('bank_branch_id')
                    ->label('Branch')
                    ->relationship('bankBranch', 'name')
                    ->native(false)
                    ->preload()
                    ->searchable(),
            ]);
    }

    public function getDisbursementSummary(): array
    {
        $start = Carbon::create($this->filterYear, $this->filterMonth)->startOfMonth();
        $end = Carbon::create($this->filterYear, $this->filterMonth)->endOfMonth();
        $statuses = $this->disbursedStatuses();

        $newLoans = Loan::withoutGlobalScope(ActiveLoanScope::class)
            ->whereIn('status', $statuses)
            ->where('new_loan', true)
            ->where('rolled', false)
            ->whereBetween('given_date', [$start, $end])
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(loan_amount), 0) as total')
            ->first();

        $oldLoans = Loan::withoutGlobalScope(ActiveLoanScope::class)
            ->whereIn('status', $statuses)
            ->where('old_loan', true)
            ->where('rolled', false)
            ->where('is_from_top_up', false)
            ->whereBetween('given_date', [$start, $end])
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(loan_amount), 0) as total')
            ->first();

        $refinanced = Loan::withoutGlobalScope(ActiveLoanScope::class)
            ->whereIn('status', $statuses)
            ->where('is_from_top_up', true)
            ->where('rolled', false)
            ->whereBetween('given_date', [$start, $end])
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(loan_amount), 0) as total')
            ->first();

        $grandTotal = Loan::withoutGlobalScope(ActiveLoanScope::class)
            ->whereIn('status', $statuses)
            ->where('rolled', false)
            ->whereBetween('given_date', [$start, $end])
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(loan_amount), 0) as total, COALESCE(SUM(loan_interest), 0) as interest_total')
            ->first();

        return [
            'new_loans'   => ['count' => (int) ($newLoans->cnt ?? 0),   'total' => (float) ($newLoans->total ?? 0)],
            'old_loans'   => ['count' => (int) ($oldLoans->cnt ?? 0),   'total' => (float) ($oldLoans->total ?? 0)],
            'refinanced'  => ['count' => (int) ($refinanced->cnt ?? 0), 'total' => (float) ($refinanced->total ?? 0)],
            'grand_total' => [
                'count'    => (int) ($grandTotal->cnt ?? 0),
                'total'    => (float) ($grandTotal->total ?? 0),
                'interest' => (float) ($grandTotal->interest_total ?? 0),
            ],
        ];
    }
}
