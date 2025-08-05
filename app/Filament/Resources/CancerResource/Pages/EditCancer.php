<?php

namespace App\Filament\Resources\CancerResource\Pages;

use App\Filament\Resources\CancerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCancer extends EditRecord
{
    protected static string $resource = CancerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
