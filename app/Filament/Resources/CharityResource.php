<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CharityResource\Pages;
use App\Filament\Resources\CharityResource\RelationManagers;
use App\Filament\Resources\CharityResource\RelationManagers\DonationsRelationManager;
use App\Models\Charity;
use App\Models\Country;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CharityResource extends Resource
{
    protected static ?string $model = Charity::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 13;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.charities');
    }

    public static function getModelLabel(): string
    {
        return __('dashboard.charity');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dashboard.charities');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('country_id')
                    ->label(__('dashboard.country'))
                    ->required()
                    ->options(Country::all()->pluck('name_ar', 'id'))
                    ->live()
                    ->searchable()->required(),
                FileUpload::make('charity_logo_ar')
                    ->columnSpan(['md' => 2, 'xl' => 2])
                    ->label(__('dashboard.charity_logo_ar'))
                    ->visibility('public')->image()
                    ->imageEditor()
                    ->maxSize(2048)
                    ->required(),
                FileUpload::make('charity_logo_en')
                    ->columnSpan(['md' => 2, 'xl' => 2])
                    ->label(__('dashboard.charity_logo_en'))
                    ->visibility('public')->image()
                    ->imageEditor()
                    ->maxSize(2048)
                    ->required(),
                Forms\Components\TextInput::make('charity_name_ar')
                    ->required()
                    ->maxLength(255)
                    ->label(__('dashboard.charity_name_ar')),
                Forms\Components\TextInput::make('charity_name_en')
                    ->required()
                    ->maxLength(255)
                    ->label(__('dashboard.charity_name_en')),
                Repeater::make('donations')
                    ->required(fn ($context) => $context === 'create')
                    ->hiddenOn(['edit'])
                    ->label(__('dashboard.donation_values'))
                    ->relationship('donations')
                    ->schema(
                        [
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
                        ]
                    )->columns(2)
                    ->columnSpan('full')->grid(2),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('charity_logo_ar')->label(__('dashboard.charity_logo_ar')),
                TextColumn::make('charity_name_ar')->label(__('dashboard.charity_name_ar')),
                TextColumn::make('country.name_ar')->label(__('dashboard.country'))

            ])
            ->filters([])
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
            DonationsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCharities::route('/'),
            'create' => Pages\CreateCharity::route('/create'),
            'edit' => Pages\EditCharity::route('/{record}/edit'),
        ];
    }
}
