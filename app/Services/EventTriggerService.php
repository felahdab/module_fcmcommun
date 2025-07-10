<?php

namespace Modules\FcmCommun\Services;

//use Modules\FcmCommun\DataObjects\UserGeneratedEvent;

use Illuminate\Support\Facades\Log;
use Modules\FcmCentral\Events\UserGeneratedEvent;

class EventTriggerService
{
    
    /**
     * Déclenche un événement avec les données fournies.
     *
     * @param array $data
     * @param object $record
     * @return void
     */
    public static function triggerEvent(array $data, object $record): void
    {

        $prefix = UserGeneratedEvent::getPrefixTable();
        Log::info('Prefixe:', ["prefix" => $prefix]);
         // Init Event
         $eventService = new EventDataService(
            new EventBuilderService(
                new EventTypeManager()
            ),
            $prefix
        );

        // Construire les données de l'événement
        Log::info('EventTrigger appele',['data'=>$data,'record'=>$record]);
        $eventData = $eventService->buildEventData($data, $record);

        if (!empty($eventData['eventTypes'])) {
            // Créer l'événement
            $event = new UserGeneratedEvent(
                event_type: $eventData['eventTypes'],
                user_uuid: auth()->user()->uuid,
                marin_uuid: $eventData['marinUuid'],
                object_class: $eventData['objectClass'],
                object_uuid: $eventData['objectUuid'],
                detail: $eventData['details'],
                data: $data,
                prefixTable: $prefix,
                record: $record,
            );

            Log::info('Event cree', ['event'=>$event]);
           //dd($event);
            // Déclencher l'événement
            event($event);
            //Log::warning('Aucun type event trouvé',['data'=>$data]);
        }
    }
}
