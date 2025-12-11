<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Enums\LoanStatus;
use App\Models\Customer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        $customerId = request()->input('customer_id');
        $productId = request()->input('product_id');
        $bankId = request()->input('bank_id');
        $bankBranchId = request()->input('bank_branch_id');
        $customer = $customerId ? Customer::find($customerId) : null;
        return $schema
            ->components([
                Section::make('Customer Information')->schema([
                    TextInput::make('customer_name')
                        ->label('Customer')
                        ->disabled()
                        ->default(fn () => request()->input('customer_name')),
                    TextInput::make('product_name')
                        ->label('Loan Product')
                        ->disabled()
                        ->default(fn () => request()->input('product_name')),
                    TextInput::make('bank_name')
                        ->label('Bank')
                        ->disabled()
                        ->default(fn () => request()->input('bank_name')),
                    TextInput::make('bank_branch_name')
                        ->label('Bank Branch')
                        ->disabled()
                        ->default(fn () => request()->input('bank_branch_name')),
                    TextInput::make('loan_limit')
                        ->label('Loan Limit')
                        ->numeric()
                        ->disabled()
                        ->default(fn () => request()->input('loan_limit')),


                    TextInput::make('customer_id')
                        ->hidden()
                        ->default($customerId),
                    TextInput::make('product_id')
                        ->hidden()
                        ->default($productId),
                    TextInput::make('bank_id')
                        ->hidden()
                        ->default($bankId),
                    TextInput::make('bank_branch_id')
                        ->hidden()
                        ->default($bankBranchId),
                ])->columns(2)->columnSpanFull(),

                Section::make('Loan Details')->schema([
                    TextInput::make('loan_amount')
                        ->required()
                        ->numeric()
                        ->default(0.0),
                    TextInput::make('loan_period')
                        ->required()
                        ->numeric()
                        ->default(1),
                    TextInput::make('agent')
                        ->required()
                        ->numeric(),
                    TextInput::make('temp_agent')
                        ->numeric()
                        ->default(null),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
