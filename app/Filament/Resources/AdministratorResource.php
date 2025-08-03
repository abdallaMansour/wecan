<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdministratorResource\Pages;
use App\Filament\Resources\AdministratorResource\RelationManagers;
use App\Models\Administrator;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AdministratorResource extends Resource
{
    protected static ?string $model = Administrator::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?int $navigationSort = 7;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.administrators');
    }

    public static function getModelLabel(): string
    {
        return __('dashboard.administrator');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.administrators');
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
                Textarea::make('description')
                    ->rows(5)
                    ->label(__('dashboard.description')),
                FileUpload::make('image')
                    ->label(__('dashboard.image'))
                    ->visibility('public')->image()
                    ->imageEditor()
                    ->maxSize(2048)
                    ->required(),
                Toggle::make('visible')
                    ->label(__('dashboard.visible'))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label(__('dashboard.image')),
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
            'index' => Pages\ListAdministrators::route('/'),
            'create' => Pages\CreateAdministrator::route('/create'),
            'edit' => Pages\EditAdministrator::route('/{record}/edit'),
        ];
    }
}
