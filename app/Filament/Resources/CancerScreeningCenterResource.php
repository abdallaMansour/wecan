<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CancerScreeningCenterResource\Pages;

use App\Models\CancerScreeningCenter;
use App\Models\Country;
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CancerScreeningCenterResource extends Resource
{
    protected static ?string $model = CancerScreeningCenter::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.cancer_screening_centers');
    }

    public static function getModelLabel(): string
    {
        return __('dashboard.cancer_screening_center');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.cancer_screening_centers');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('hospital_logo')
                    ->columnSpan(['md' => 2, 'xl' => 2])
                    ->label(__('dashboard.hospital_logo'))
                    ->visibility('public')->image()
                    ->imageEditor()
                    ->maxSize(2048)
                    ->required(),
                Forms\Components\TextInput::make('hospital_name_ar')
                    ->required()
                    ->maxLength(255)
                    ->label(__('dashboard.hospital_name_ar')),
                Forms\Components\TextInput::make('hospital_name_en')
                    ->required()
                    ->maxLength(255)
                    ->label(__('dashboard.hospital_name_en')),

                Select::make('country_id')
                    ->label(__('dashboard.country'))
                    ->required()
                    ->options(Country::all()->pluck('name_ar', 'id'))
                    ->live()
                    ->searchable()->required(),
                Select::make('region_id')
                    ->required()
                    ->label(__('dashboard.region'))
                    ->options(fn (Get $get) => Region::query()
                        ->where('country_id', $get('country_id'))
                        ->pluck('name_ar', 'id'))
                    ->searchable()->required(),
                Forms\Components\TextInput::make('phone_number')
                    ->maxLength(255)
                    ->label(__('dashboard.phone_number')),
                Forms\Components\TextInput::make('website')
                    ->label(__('dashboard.website')),
                Forms\Components\TextInput::make('google_map_link')
                    ->required()
                    ->columnSpan(['md' => 2, 'xl' => 2])
                    ->label(__('dashboard.google_map_link')),
                Toggle::make('visible')
                    ->label(__('dashboard.visible'))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('hospital_logo')->label(__('dashboard.hospital_logo')),
                TextColumn::make('hospital_name_ar')->label(__('dashboard.hospital_name_ar')),
                TextColumn::make('hospital_name_en')->label(__('dashboard.hospital_name_en')),
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
            'index' => Pages\ListCancerScreeningCenters::route('/'),
            'create' => Pages\CreateCancerScreeningCenter::route('/create'),
            'edit' => Pages\EditCancerScreeningCenter::route('/{record}/edit'),
        ];
    }
}
