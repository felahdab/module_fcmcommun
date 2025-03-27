<?php

namespace Modules\FcmCommun\Filament\Fcmcommun\Resources;

use Modules\FcmCommun\Filament\Fcmcommun\Resources\CohorteResource\Pages;
use Modules\FcmCommun\Filament\Fcmcommun\Resources\CohorteResource\RelationManagers;
use Modules\FcmCommun\Models\Cohorte;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;

class CohorteResource extends Resource
{
    protected static ?string $model = Cohorte::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Liste Cohortes';
    
    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    } 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('libelle_long')
                    ->maxLength(255)
                    ->default(null),
                TextInput::make('libelle_court')
                    ->maxLength(255)
                    ->default(null),
                DatePicker::make('date_sortie_cours'),    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('libelle_long')
                    ->searchable(),
                TextColumn::make('libelle_court')
                    ->searchable(),
                TextColumn::make('cohorte_count')
                ->label('Nb Marins')
                //->counts('fcmCentralMarins')
                ->getStateUsing(function (Cohorte $record){
                    return $record->fcmCentralMarins()->count();
                } )
                ->sortable()
                ->badge(),
                TextColumn::make('date_sortie_cours')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date()
                    ->sortable(),
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
            'index' => Pages\ListCohortes::route('/'),
            'create' => Pages\CreateCohorte::route('/create'),
            'edit' => Pages\EditCohorte::route('/{record}/edit'),
        ];
    }
}
