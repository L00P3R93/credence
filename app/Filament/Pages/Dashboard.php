<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->columns([
           Section::make()->schema([
               Select::make('loan_products')
                   ->label('Loan Products')
                   ->options([
                       'Personal Loan' => 'Personal Loan',
                       'Business Loan' => 'Business Loan',
                   ])
                   ->native(false),
               DatePicker::make('start_date')
                   ->maxDate(fn (Get $get) => $get('end_date') ?: now()),
               DatePicker::make('end_date')
                   ->minDate(fn (Get $get) => $get('start_date') ?: now())
                   ->maxDate(now()),
           ])->columns(3)->columnSpanFull()
        ]);
    }
}
