<?php

namespace App\Filament\Resources\Refinances\Pages;

use App\Filament\Resources\Refinances\RefinanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRefinance extends EditRecord
{
    protected static string $resource = RefinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
