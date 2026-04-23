<?php

namespace App\Filament\Resources\Customers;

use App\Enums\FaIcon;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\RelationManagers\LoansRelationManager;
use App\Filament\Resources\Customers\RelationManagers\MediaRelationManager;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Customers\Schemas\CustomerInfolist;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Enums\LoanStatus;
use App\Models\Customer;
use App\Models\Loan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = FaIcon::USER_GROUP;
    protected static string | UnitEnum | null $navigationGroup = 'Leads & Customers';
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LoansRelationManager::class,
            MediaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'view' => ViewCustomer::route('/{record}'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'id_no', 'phone', 'phone_alt', 'work_email', 'personal_email'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(['bank', 'bankBranch'])
            ->addSelect([
                'customers.*',
                'active_loan_status' => Loan::select('status')
                    ->whereColumn('customer_id', 'customers.id')
                    ->whereIn('status', [
                        LoanStatus::DISBURSED->value,
                        LoanStatus::OVERDUE->value,
                        LoanStatus::PAST_OVERDUE->value,
                        LoanStatus::DUE_ROLL->value,
                    ])
                    ->latest('id')
                    ->limit(1),
            ]);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        assert($record instanceof Customer);

        /** @var \App\Models\Bank|null $bank */
        $bank = $record->bank;
        /** @var \App\Models\BankBranch|null $branch */
        $branch = $record->bankBranch;

        $bankBranch = implode(' | ', array_filter([
            $bank?->getAttribute('name'),
            $branch?->getAttribute('name'),
        ]));

        $rawStatus = $record->getAttribute('active_loan_status');
        $loanStatus = $rawStatus
            ? LoanStatus::from((string) $rawStatus)->getLabel()
            : 'No Active Loan';

        return [
            'National ID'   => $record->id_no ?? '—',
            'Bank | Branch' => $bankBranch ?: '—',
            'Loan Status'   => $loanStatus,
        ];
    }
}
