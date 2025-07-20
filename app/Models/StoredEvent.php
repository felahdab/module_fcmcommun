<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;

use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCommun\DataObjects\RemoteGeneratedEvent;

class StoredEvent extends Model
{

    protected $fillable = [
        'id',
        'event_uuid',
        'event_type',
        'user_uuid',
        'object_class',
        'object_uuid',
        'detail'
    ];

    protected function casts(): array
    {
        return [
            'detail' => 'array',
        ];
    }

    public static function createFromUserGeneratedEvent(UserGeneratedEvent|RemoteGeneratedEvent $event)
    {
        $data = $event->toArray();
        $detail = $data["detail"];
        unset($data["detail"]);
        $newmodel = static::create($data);

        $newmodel->detail=$detail;
        $newmodel->save();
    }

}
