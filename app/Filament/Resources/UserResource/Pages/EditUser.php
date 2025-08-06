<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Stichoza\GoogleTranslate\GoogleTranslate;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['name']))
            $data['name_en'] = GoogleTranslate::trans($data['name'], 'en', 'ar');
        else
            $data['name'] = GoogleTranslate::trans($data['name_en'], 'ar', 'en');

        if (!empty($data['profession_ar']))
            $data['profession_en'] = GoogleTranslate::trans($data['profession_ar'], 'en', 'ar');
        elseif (!empty($data['profession_en']))
            $data['profession_ar'] = GoogleTranslate::trans($data['profession_en'], 'ar', 'en');

        if (!empty($data['hospital_ar']))
            $data['hospital_en'] = GoogleTranslate::trans($data['hospital_ar'], 'en', 'ar');
        elseif (!empty($data['hospital_en']))
            $data['hospital_ar'] = GoogleTranslate::trans($data['hospital_en'], 'ar', 'en');

        return $data;
    }
}
