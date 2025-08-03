<?php

namespace App\Filament\Resources\CharityResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DonationsRelationManager extends RelationManager
{
    protected static string $relationship = 'donations';

    public static function getTitle(Model $ownerRecord, ?string $pageClass = null): string
    {
        return __('dashboard.donations');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('donation_value')
                    ->label(__('dashboard.donation_value'))
                    ->required(),
                Select::make('country_code')
                    ->label(__('dashboard.country_code'))
                    ->options([
                        'DZ' => 'DZ الجزائر',
                        'BH' => 'BH البحرين',
                        'KM' => 'KM جزر القمر',
                        'DJ' => 'DJ جيبوتي',
                        'EG' => 'EG مصر',
                        'IQ' => 'IQ العراق',
                        'JO' => 'JO الأردن',
                        'KW' => 'KW الكويت',
                        'LB' => 'LB لبنان',
                        'LY' => 'LY ليبيا',
                        'MR' => 'MR موريتانيا',
                        'MA' => 'MA المغرب',
                        'OM' => 'OM عمان',
                        'PS' => 'PS فلسطين',
                        'QA' => 'QA قطر',
                        'SA' => 'SA السعودية',
                        'SO' => 'SO الصومال',
                        'SD' => 'SD السودان',
                        'SY' => 'SY سوريا',
                        'TN' => 'TN تونس',
                        'AE' => 'AE الإمارات العربية المتحدة',
                        'YE' => 'YE اليمن',
                    ])
                    ->required(),
                TextInput::make('sms_code')
                    ->label(__('dashboard.sms_code'))
                    ->required(),
                TextInput::make('message_code')
                    ->label(__('dashboard.message_code'))
                    ->required(),
                FileUpload::make('telecom_logo')
                    ->label(__('dashboard.telecom_logo'))
                    ->visibility('public')->image()
                    ->imageEditor()
                    ->maxSize(2048)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('donation_value')
            ->columns([
                Tables\Columns\TextColumn::make('donation_value')->label(__('dashboard.donation_value')),
                Tables\Columns\TextColumn::make('country_code')->label(__('dashboard.country_code')),
                Tables\Columns\TextColumn::make('sms_code')->label(__('dashboard.sms_code')),
                Tables\Columns\TextColumn::make('message_code')->label(__('dashboard.message_code')),
                ImageColumn::make('telecom_logo')->label(__('dashboard.telecom_logo'))

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label(__('dashboard.add_donation')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
