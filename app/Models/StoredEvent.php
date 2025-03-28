<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCommun\DataObjects\RemoteGeneratedEvent;

use Modules\FcmCommun\Traits\HasTablePrefix;

class StoredEvent extends Model
{
    use HasFactory;
    use HasTablePrefix;


    protected $primaryKey = 'id';

    protected $fillable = [
        'event_uuid',
        'event_type',
        'marin_id',
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
