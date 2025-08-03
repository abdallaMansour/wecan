<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactUsResource\Pages;
use App\Filament\Resources\ContactUsResource\RelationManagers;
use App\Models\ContactUs;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactUsResource extends Resource
{
    protected static ?string $model = ContactUs::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.contact_us');
    }

    public static function getModelLabel(): string
    {
        return __('dashboard.contact_us');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.contact_us');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DateTimePicker::make('datetime')
                    ->required()
                    ->label(__('dashboard.datetime')),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label(__('dashboard.name'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->label(__('dashboard.email'))
                    ->maxLength(255),
                Textarea::make('subject')
                    ->rows(5)
                    ->label(__('dashboard.subject')),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('dashboard.name')),
                Tables\Columns\TextColumn::make('email')->label(__('dashboard.email')),
                Tables\Columns\TextColumn::make('datetime')->label(__('dashboard.datetime')),
            ])
            ->filters([
                //
            ])
            ->actions([])
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
            'index' => Pages\ListContactUs::route('/'),
            'create' => Pages\CreateContactUs::route('/create'),
            'view' => Pages\ViewContactUs::route('/{record}'),

        ];
    }
}
