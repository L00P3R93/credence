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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class Overdues extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | UnitEnum | null $navigationGroup = 'Collections';

    protected static string | BackedEnum | null $navigationIcon = 'hugeicons-alert-02';

    protected static ?string $navigationLabel = 'Overdues';

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Overdues';

    protected string $view = 'filament.pages.overdues';

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
                    ->where('status', LoanStatus::OVERDUE)
                    ->withSum('payments', 'amount')
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

                Filter::make('due_date_month_year')
                    ->label('Due Date Period')
                    ->form([
                        Select::make('month')
                            ->label('Month')
                            ->options([
                                '1' => 'January', '2' => 'February', '3' => 'March',
                                '4' => 'April', '5' => 'May', '6' => 'June',
                                '7' => 'July', '8' => 'August', '9' => 'September',
                                '10' => 'October', '11' => 'November', '12' => 'December',
                            ])
                            ->native(false)
                            ->placeholder('All Months'),

                        Select::make('year')
                            ->label('Year')
                            ->options(
                                collect(range(Carbon::now()->year - 3, Carbon::now()->year + 1))
                                    ->mapWithKeys(fn (int $y) => [$y => $y])
                                    ->all()
                            )
                            ->native(false)
                            ->placeholder('All Years'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['month'] ?? null, fn (Builder $q, $month) => $q->whereMonth('due_date', $month))
                            ->when($data['year'] ?? null, fn (Builder $q, $year) => $q->whereYear('due_date', $year));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['month'] ?? null) {
                            $indicators[] = 'Month: ' . Carbon::create()->month((int) $data['month'])->format('F');
                        }
                        if ($data['year'] ?? null) {
                            $indicators[] = 'Year: ' . $data['year'];
                        }
                        return $indicators;
                    }),
            ]);
    }
}
