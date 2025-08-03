<?php

namespace App\Filament\Resources\CancerScreeningCenterResource\Pages;

use App\Filament\Resources\CancerScreeningCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCancerScreeningCenter extends EditRecord
{
    protected static string $resource = CancerScreeningCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
