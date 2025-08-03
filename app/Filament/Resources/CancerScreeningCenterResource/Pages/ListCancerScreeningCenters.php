<?php

namespace App\Filament\Resources\CancerScreeningCenterResource\Pages;

use App\Filament\Resources\CancerScreeningCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCancerScreeningCenters extends ListRecords
{
    protected static string $resource = CancerScreeningCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
