<?php

namespace Modules\FcmCommun\Filament\Fcmcommun\Resources\CohorteResource\Pages;

use Modules\FcmCommun\Filament\Fcmcommun\Resources\CohorteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCohorte extends EditRecord
{
    protected static string $resource = CohorteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
