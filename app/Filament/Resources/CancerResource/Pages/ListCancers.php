<?php

namespace App\Filament\Resources\CancerResource\Pages;

use App\Filament\Resources\CancerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCancers extends ListRecords
{
    protected static string $resource = CancerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
