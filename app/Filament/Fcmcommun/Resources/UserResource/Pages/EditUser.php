<?php

namespace Modules\FcmCommun\Filament\Fcmcommun\Resources\UserResource\Pages;

use Modules\FcmCommun\Filament\Fcmcommun\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

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
