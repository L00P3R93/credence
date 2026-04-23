<?php

namespace App\Filament\Pages;

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
use BackedEnum;
use UnitEnum;

class CollectionsList extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | UnitEnum | null $navigationGroup = 'Loan Management';

    protected static string | BackedEnum | null $navigationIcon = 'hugeicons-money-receive-square';

    protected static ?string $navigationLabel = 'Collections List';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Collections List';

    protected string $view = 'filament.pages.collections-list';

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Loan::query()
                    ->withoutGlobalScope(ActiveLoanScope::class)
                    ->whereIn('status', [
                        LoanStatus::DISBURSED,
                        LoanStatus::OVERDUE,
                        LoanStatus::PAST_OVERDUE,
                        LoanStatus::DUE_ROLL,
                    ])
                    ->whereBetween('due_date', [
                        Carbon::now()->startOfMonth(),
                        Carbon::now()->endOfMonth(),
                    ])
                    ->withSum('payments', 'amount')
                    ->whereRaw(
                        '(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.loan_id = loans.id) < loans.loan_interest'
                    )
                    ->with(['customer', 'bank', 'bankBranch'])
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

                TextColumn::make('bankBranch.name')
                    ->label('Branch')
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

                TextColumn::make('loan_total')
                    ->label('Loan Total')
                    ->numeric(thousandsSeparator: ',')
                    ->sortable(),

                TextColumn::make('payments_sum_amount')
                    ->label('Paid')
                    ->numeric(thousandsSeparator: ',')
                    ->default(0)
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Balance')
                    ->numeric(thousandsSeparator: ',')
                    ->state(fn (Loan $record): float => $record->loan_total - ($record->payments_sum_amount ?? 0))
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw(
                            "(loan_total - COALESCE((SELECT SUM(amount) FROM payments WHERE payments.loan_id = loans.id), 0)) {$direction}"
                        );
                    }),

                TextColumn::make('collection_status')
                    ->label('Status')
                    ->badge()
                    ->state(function (Loan $record): string {
                        $paid = $record->payments_sum_amount ?? 0;
                        if ($record->due_date->lte(Carbon::today()) && $paid < $record->loan_interest) {
                            return 'Due Payment';
                        }
                        return 'Over Due';
                    })
                    ->color(function (Loan $record): string {
                        $paid = $record->payments_sum_amount ?? 0;
                        if ($record->due_date->lte(Carbon::today()) && $paid < $record->loan_interest) {
                            return 'info';
                        }
                        return 'danger';
                    }),
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

                SelectFilter::make('status')
                    ->label('Loan Status')
                    ->options([
                        LoanStatus::DISBURSED->value => LoanStatus::DISBURSED->getLabel(),
                        LoanStatus::OVERDUE->value => LoanStatus::OVERDUE->getLabel(),
                        LoanStatus::PAST_OVERDUE->value => LoanStatus::PAST_OVERDUE->getLabel(),
                        LoanStatus::DUE_ROLL->value => LoanStatus::DUE_ROLL->getLabel(),
                    ])
                    ->native(false),
            ]);
    }
}
