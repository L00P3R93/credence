<?php

namespace App\Filament\Resources\Refinances\Pages;

use App\Filament\Resources\Refinances\RefinanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRefinances extends ListRecords
{
    protected static string $resource = RefinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
