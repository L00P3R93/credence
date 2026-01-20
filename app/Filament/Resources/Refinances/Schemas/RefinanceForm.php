<?php

namespace App\Filament\Resources\Refinances\Schemas;

use App\Enums\LoanStatus;
use App\Enums\RefinanceStatus;
use App\Models\Loan;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class RefinanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::getFormSchema());
    }

    public static function getFormSchema(?Loan $loan = null): array
    {
        $isInActionContext = $loan !== null;
        $maxAmount = $isInActionContext ? $loan->refinanceAmount() : 0;
        return [
            $isInActionContext
                ? Hidden::make('loan_id')->default($loan->id)
                : Select::make('loan_id')
                    ->relationship('loan', 'id', fn (Builder $query) => $query->whereIn('status', [LoanStatus::DISBURSED, LoanStatus::OVERDUE])->latest()->limit(10))
                    ->searchable()
                    ->native(false)
                    ->prefixIcon('hugeicons-product-loading')
                    ->prefixIconColor('primary')
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $loan = Loan::query()->find($state);
                            $refinanceAmount = $loan->refinanceAmount();
                            $set('eligible_amount', $refinanceAmount);
                            $set('amount', $refinanceAmount);
                        }
                    })
                    ->required(),
                TextInput::make('eligible_amount')
                    ->label('Eligible Refinance Amount')
                    ->prefix('KES')
                    ->prefixIcon('hugeicons-coins-swap')
                    ->prefixIconColor('primary')
                    ->numeric()
                    ->readOnly()
                    ->default($isInActionContext ? $maxAmount : null)
                    ->visible(fn (callable $get) => $isInActionContext || $get('loan_id') !== null)
                    ->columnSpanFull(),

                TextInput::make('amount')
                    ->label('Refinance Amount')
                    ->prefix('KES')
                    ->prefixIcon('hugeicons-coins-01')
                    ->prefixIconColor('primary')
                    ->required()
                    ->minValue(1000)
                    ->maxValue($maxAmount)
                    ->numeric()
                    ->default($isInActionContext ? $maxAmount : null)
                    ->rules([
                        'required',
                        'numeric',
                        'min:1000',
                        function () use ($maxAmount) {
                            return function (string $attribute, $value, \Closure $fail) use ($maxAmount) {
                                if ($value > $maxAmount) {
                                    $fail("The refinance amount cannot exceed the eligible amount of KES ".number_format($maxAmount));
                                }
                            };
                        }
                    ]),
        ];
    }
}

