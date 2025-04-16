<?php

namespace Modules\FcmCommun\Filament\Fcmcommun\Resources\FcmMarinResource\Pages;

use Modules\FcmCommun\Filament\Fcmcommun\Resources\FcmMarinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFcmMarin extends EditRecord
{
    protected static string $resource = FcmMarinResource::class;

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
