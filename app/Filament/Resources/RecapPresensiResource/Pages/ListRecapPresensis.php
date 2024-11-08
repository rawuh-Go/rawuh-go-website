<?php

namespace App\Filament\Resources\RecapPresensiResource\Pages;

use App\Filament\Resources\RecapPresensiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRecapPresensis extends ListRecords
{
    protected static string $resource = RecapPresensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
