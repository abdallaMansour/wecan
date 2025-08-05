<?php

namespace App\Filament\Resources\CancerResource\Pages;

use Filament\Actions;
use App\Filament\Resources\CancerResource;
use Filament\Resources\Pages\CreateRecord;
use Stichoza\GoogleTranslate\GoogleTranslate;

class CreateCancer extends CreateRecord
{
    protected static string $resource = CancerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['name_ar'])) {
            $data['name_en'] = GoogleTranslate::trans($data['name_ar'], 'en', 'ar');
        } else {
            $data['name_ar'] = GoogleTranslate::trans($data['name_en'], 'ar', 'en');
        }

        return $data;
    }
}
