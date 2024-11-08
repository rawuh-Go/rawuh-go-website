<?php

namespace App\Filament\Resources\RecapPresensiResource\Pages;

use App\Filament\Resources\RecapPresensiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRecapPresensi extends EditRecord
{
    protected static string $resource = RecapPresensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
