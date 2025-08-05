<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Cancer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CancerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CancerResource\RelationManagers;

class CancerResource extends Resource
{
    protected static ?string $model = Cancer::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    public static function getNavigationLabel(): string
    {
        return __('dashboard.cancer_types');
    }

    public static function getModelLabel(): string
    {
        return __('dashboard.cancer_type');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.cancer_types');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('cancer_image')
                    ->columnSpan(['md' => 2, 'xl' => 2])
                    ->label(__('dashboard.cancer_image'))
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->maxSize(2048)
                    ->required()
                    ->deleteUploadedFileUsing(function ($file) {
                        Storage::disk('public')->delete($file);
                    }),
                Forms\Components\TextInput::make('name_ar')
                    ->required()
                    ->maxLength(255)
                    ->label(__('dashboard.cancer_name_ar')),
                Forms\Components\TextInput::make('name_en')
                    ->required()
                    ->maxLength(255)
                    ->label(__('dashboard.cancer_name_en')),
                Toggle::make('visible')
                    ->label(__('dashboard.visible'))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cancer_image')->label(__('dashboard.cancer_image')),
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
            'index' => Pages\ListCancers::route('/'),
            'create' => Pages\CreateCancer::route('/create'),
            'edit' => Pages\EditCancer::route('/{record}/edit'),
        ];
    }
}
