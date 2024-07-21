<?php

namespace Modules\FcmCommun\Filament\Fcmcommun\Resources\UserResource\Pages;

use Modules\FcmCommun\Filament\Fcmcommun\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
