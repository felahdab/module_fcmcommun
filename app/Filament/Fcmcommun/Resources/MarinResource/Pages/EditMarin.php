<?php

namespace Modules\FcmCommun\Filament\Fcmcommun\Resources\MarinResource\Pages;

use Modules\FcmCommun\Filament\Fcmcommun\Resources\MarinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarin extends EditRecord
{
    protected static string $resource = MarinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public static function getWidgets(): array
    {
        return [
        ];
    }
}
