<?php

namespace App\Filament\Resources\CancerResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\CancerResource;
use Stichoza\GoogleTranslate\GoogleTranslate;

class EditCancer extends EditRecord
{
    protected static string $resource = CancerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['name_ar'])) {
            $data['name_en'] = GoogleTranslate::trans($data['name_ar'], 'en', 'ar');
        } else {
            $data['name_ar'] = GoogleTranslate::trans($data['name_en'], 'ar', 'en');
        }

        return $data;
    }
}
