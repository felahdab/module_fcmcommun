<?php

namespace Modules\FcmCommun\Filament\Fcmcommun\Resources;

use Modules\FcmCommun\Filament\Fcmcommun\Resources\FcmMarinResource\Pages;
use Modules\RH\Filament\RH\Resources\MarinResource\Pages as RhPages;

use Modules\FcmCommun\Filament\Fcmcommun\Resources\FcmMarinResource\RelationManagers;


use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCommun\Models\Marin as Marin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Pages\Actions;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Modules\FcmCentral\Events\SaveFcmMarinEvent;
use Illuminate\Support\Facades\DB;



class FcmMarinResource extends Resource
{
    
    protected static ?string $model = Marin::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Liste Marins FCM';

    public static function getNavigationBadge(): ?string
    {
        //return static::$model::count();
        return \Modules\FcmCentral\Models\FcmMarin::count();
    }

    // Supprimer bouton Create
    // public static function canCreate(): bool
    // {
    //     return false;
    // }

   



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
             
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('marin.nom')
                    ->label('Nom marin')
                    ->getStateUsing(function ($record){
                            return $record->nom.' '.$record->prenom;
                    })
                    ->searchable()
                    // Empeche de cliquer pour edit
                   // ->disabledClick()
                    ,

                    TextColumn::make('grade.libelle_court')
                    ->searchable()
                    ->badge(),
                TextColumn::make('specialite.libelle_court')
                    ->searchable()
                    ->badge(),
                TextColumn::make('brevet.libelle_court')
                    ->searchable()
                    ->badge(),
                TextColumn::make('unite.libelle_court')
                    ->searchable()
                    ->badge(),
                TextColumn::make('nid')
                    ->searchable(),
               
                TextColumn::make('uuid')
                ->toggleable(isToggledHiddenByDefault: true)
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('cohorte.libelle_court')
                        ->label('Cohorte Central')
                        ->searchable()
                        ->getStateUsing(function (Marin $record){
                            // recuperer le premier mentor associé

                            $cohorte = $record->fcmCentralCohorte ?? null;
                            return $cohorte ? $cohorte->libelle_court  : ' / ';
                        }) ,
                        IconColumn::make('fcmCentral')
                        ->label('FCM Central')
                        ->boolean()
                        ->getStateUsing(function (Marin $record){
                            return $record->fcmCentralMarin()->exists();
                        }) ,
                    TextColumn::make('mentor.nom')
                        ->label('Mentor Central')
                        ->searchable()
    
                    ->getStateUsing(function (Marin $record){
                        // recuperer le premier mentor associé
                        $mentor = $record->directMentor ?? null;
                        return $mentor ? $mentor->nom .' '.$mentor->prenom : ' / ';
                    }) ,
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->url(fn ($record)=>FcmMarinResource::getUrl('edit', ['record'=> $record->id,'type'=>'fcmcommun']))
                ->label('')
                ->icon('heroicon-o-pencil'),
                Tables\Actions\Action::make('assignCohorte')
                ->label('Assigner Cohorte et Mentor')
                ->form([
                    Select::make('cohorte_id')
                    //->relationship('id','libelle_court')
                    ->options(Cohorte::pluck('libelle_court','id'))
                    ->searchable()
                    ->required()
                    ->default(function ($record){
                        return $record->fcmCentralCohorte->id ?? null;
                    }),
                    Select::make('mentor_id')
                    //->options(Marin::pluck('nom','id'))
                    // Recherche dans la base en concat
                    ->options(Marin::select(
                        'rh_marins.id',
                        DB::raw("CONCAT(rh_marins.nom, ' ', rh_marins.prenom,' (', rh_grades.libelle_court, ')') as full_name"))
                        ->leftjoin('rh_grades','rh_marins.grade_id','=','rh_grades.id')
                        ->pluck('full_name','id'))
                    // Methode full name dans RH Marins
                    //->options(Marin::pluck('full_name2','id'))
                    ->searchable()
                    //->required()
                    ->default(function ($record){
                        return $record->directMentor->id ?? null;
                    }),
                ])
                ->action(function (Marin $record, array $data){
                    // Declencher Event
                   event(new SaveFcmMarinEvent($record, $data));
                })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                   // Tables\Actions\DeleteBulkAction::make(),
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
           
            'index' => Pages\ListFcmMarins::route('/'),
            //'create' => Pages\CreateMarin::route('/create'),
            //'edit' => Pages\EditMarin::route('/{record}/edit'),
            // Redirection vers pages RH MarinResource avec arg from pour dire d'ou on provient
            'create' => RhPages\CreateMarin::route('/create'),
            'edit' =>   RhPages\EditMarin::route('/{record}/edit'),
            
            'livret' => Pages\LivretDeTransformation::route('/{record}/livret-de-fcm')
        ];
    }
}
