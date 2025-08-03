<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegionResource\Pages;
use App\Filament\Resources\RegionResource\RelationManagers;
use App\Models\Country;
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RegionResource extends Resource
{
    protected static ?string $model = Region::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?int $navigationSort = 9;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.regions');
    }

    public static function getModelLabel(): string
    {
        return __('dashboard.region');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.regions');
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
                Select::make('country_id')
                    ->label(__('dashboard.country'))
                    ->options(Country::all()->pluck('name_ar', 'id'))
                    ->searchable()->required(),
                Toggle::make('visible')
                    ->label(__('dashboard.visible'))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('country.name_ar')->label(__('dashboard.country')),
                TextColumn::make('name_ar')->label(__('dashboard.name_ar')),
                TextColumn::make('name_en')->label(__('dashboard.name_en')),
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
            'index' => Pages\ListRegions::route('/'),
            'create' => Pages\CreateRegion::route('/create'),
            'edit' => Pages\EditRegion::route('/{record}/edit'),
        ];
    }
}
