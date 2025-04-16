<?php

namespace Modules\FcmCommun\Filament\Fcmcommun\Resources\FcmMarinResource\Pages;

use Modules\FcmCommun\Filament\Fcmcommun\Resources\FcmMarinResource;

use Filament\Resources\Pages\ViewRecord;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Livewire;

use Modules\FcmCommun\Http\Livewire\LivretDeTransformation as LivretDeTransformationLivewire;

class LivretDeTransformation extends ViewRecord
{
    protected static string $resource = FcmMarinResource::class;

    public function form(Form $form): Form
    {
        return $form
        ->schema([
            Section::make('Informations sur le marin')
                //->description('Prevent abuse by limiting the number of requests per period')
                ->schema([
                    Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->maxLength(255)
                            ->default(null),
                        Forms\Components\TextInput::make('prenom')
                            ->required()
                            ->maxLength(100)
                            ->default(''),
                    ]),
                ]),
            Section::make('Livret de FCM')
                //->description('Prevent abuse by limiting the number of requests per period')
                ->schema([    
                    //Livewire::make(LivretDeTransformationLivewire::class, ["uuid" => "2bb1800f-c247-434b-abef-4eab4cff0836"]),
                    Livewire::make(LivretDeTransformationLivewire::class, ["uuid" => $this->record->uuid]),
                ])
            ]);    
    }

    protected function getFooterWidgets(): array
    {
        return [
            //LivretDeTransformationWidget::class
        ];
    }
}
