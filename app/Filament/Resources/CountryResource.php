<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Filament\Resources\CountryResource\RelationManagers;
use App\Models\Country;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?int $navigationSort = 8;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.countries');
    }

    public static function getModelLabel(): string
    {
        return __('dashboard.country');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.countries');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_ar')
                    ->required()
                    ->maxLength(255)
                    ->label(__('dashboard.name_ar')),
                Forms\Components\TextInput::make('name_en')
                    ->required()
                    ->maxLength(255)
                    ->label(__('dashboard.name_en')),
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
                Toggle::make('visible')
                    ->label(__('dashboard.visible'))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_ar')->label(__('dashboard.name_ar')),
                TextColumn::make('name_en')->label(__('dashboard.name_en')),
                TextColumn::make('country_code')->label(__('dashboard.country_code')),
                ToggleColumn::make('visible')->label(__('dashboard.visible'))
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
