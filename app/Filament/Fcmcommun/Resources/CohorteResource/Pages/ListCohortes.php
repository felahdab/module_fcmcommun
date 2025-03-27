<?php

namespace Modules\FcmCommun\Filament\Fcmcommun\Resources\CohorteResource\Pages;

use Modules\FcmCommun\Filament\Fcmcommun\Resources\CohorteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCohortes extends ListRecords
{
    protected static string $resource = CohorteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
