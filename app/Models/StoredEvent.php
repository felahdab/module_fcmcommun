<?php

namespace Modules\FcmCommun\Models;


use Illuminate\Database\Eloquent\Model;



use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCommun\DataObjects\RemoteGeneratedEvent;
use Illuminate\Support\Facades\Log;



class StoredEvent extends Model
{





    protected $fillable = [
        'id',
        'event_uuid',
        'event_type',
        'marin_uuid',
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

    // Ancien code  Commandant (sauvegarde au cas ou)
    // public static function createFromUserGeneratedEvent(UserGeneratedEvent|RemoteGeneratedEvent $event)
    // {
    //     $data = $event->toArray();
    //     $detail = $data["detail"];
    //     unset($data["detail"]);
    //     $newmodel = static::create($data);

    //     $newmodel->detail=$detail;
    //     $newmodel->save();

    // }

    public static function createFromUserGeneratedEvent(UserGeneratedEvent|RemoteGeneratedEvent $event)
{
    $data = $event->toArray();

    // Vérifier si event_type est un tableau
    if (is_array($data['event_type'])) {
        foreach ($data['event_type'] as $type) {
            // Créer une entrée distincte pour chaque type d'événement
            static::create([
                'event_uuid'    => $event->event_uuid,
                'event_type'    => $type,
                'user_uuid'     => $data['user_uuid'],
                'marin_uuid'    => $data['marin_uuid'],
                'object_class'  => $data['object_class'][$type] ?? null,
                'object_uuid'   => $data['object_uuid'][$type] ?? null,
                'detail'        => $data['detail'][$type] ?? null,
            ]);
        }
        Log::info('CreateFromUserGeneratedEvent Array cree');
    } else {
        // Si event_type est une chaîne, insérer normalement
        static::create([
            'event_uuid'    => $event->event_uuid,
            'event_type'    => $data['event_type'],
            'user_uuid'     => $data['user_uuid'],
            'marin_uuid'    => $data['marin_uuid'],
            'object_class'  => $data['object_class'],
            'object_uuid'   => $data['object_uuid'],
            'detail'        => $data['detail'],
        ]);
        Log::info('CreateFromUserGeneratedEvent String cree');
    }
    
}

}
