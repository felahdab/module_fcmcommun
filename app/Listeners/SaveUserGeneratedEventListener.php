<?php

namespace Modules\FcmCommun\Listeners;

use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use  Modules\FcmCommun\Services\EventTypeManager;

class SaveUserGeneratedEventListener
{
    /**
     * Gere la Sauvegarde des datas 
     * Les Listeners se trouvent dans FcmCommun\Services\TypeEvent
     */
    public function handle(UserGeneratedEvent $event):void
    {
        // Recupere une instance via app (porbleme injonction avec method __construct)
        $eventTypeService = app(EventTypeManager::class);

        foreach($event->event_type as $type){
            $listernerClass = $eventTypeService->getEventTypeDetails($type)['listener'] ?? null;

            if ($listernerClass){
                // Appeler  dynamiquement le listener correspondant
                $listener = app($listernerClass);
                if (method_exists($listener, 'handle')){
                    $listener->handle($event);
                }
            }
        }
    }
}