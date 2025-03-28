<?php

namespace Modules\FcmCommun\Filament\Fcmcommun\Resources\MarinResource\Pages;

use Modules\FcmCommun\Filament\Fcmcommun\Resources\MarinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarins extends ListRecords
{
    protected static string $resource = MarinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
