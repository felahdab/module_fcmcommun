<?php

namespace Modules\FcmCommun\Filament\Fcmcommun\Resources\FcmMarinResource\Pages;

use Modules\FcmCommun\Filament\Fcmcommun\Resources\FcmMarinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFcmMarins extends ListRecords
{
    protected static string $resource = FcmMarinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
