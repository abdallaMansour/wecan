<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HealthTipsRelationManager extends RelationManager
{
    protected static string $relationship = 'healthTips';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('dashboard.health_tip');
    }
    public static function getModelLabel(): string
    {
        return __('dashboard.health_tip');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.health_tip');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->account_type === 'doctor';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title_ar')
                    ->label(__('dashboard.title_ar'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('title_en')
                    ->label(__('dashboard.title_en'))
                    ->maxLength(255),
                Textarea::make('details_ar')
                    ->rows(5)
                    ->columnSpan(2)
                    ->label(__('dashboard.details_ar')),
                Textarea::make('details_en')
                    ->rows(5)
                    ->columnSpan(2)
                    ->label(__('dashboard.details_en')),
                DateTimePicker::make('publish_datetime')
                    ->required()
                    ->label(__('dashboard.publish_datetime')),
                FileUpload::make('attachments')
                    ->label(__('dashboard.attachments'))
                    ->visibility('public')
                    ->multiple(),
                Select::make('tip_type')
                    ->label(__('dashboard.tip_type'))
                    ->options([
                        'Medication Tips' => __('dashboard.medication_tips'),
                        'General Tips' => __('dashboard.general_tips'),
                        'Nutrition Tips' => __('dashboard.nutrition_tips'),
                        'Dosage Tips' => __('dashboard.dosage_tips'),
                        'Other' => __('dashboard.other'),
                    ])
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('link')
                    ->label(__('dashboard.link')),
                Toggle::make('visible')
                    ->label(__('dashboard.visible'))
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title_ar')
            ->columns([
                Tables\Columns\TextColumn::make('title_ar')->label(__('dashboard.title_ar')),
                Tables\Columns\TextColumn::make('publish_datetime')->label(__('dashboard.publish_datetime')),
                ToggleColumn::make('visible')->label(__('dashboard.visible'))

            ])
            ->filters([
                //
            ])
            ->headerActions([])
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
