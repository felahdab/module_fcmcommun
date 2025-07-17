<?php

namespace Modules\FcmCommun\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\FcmCommun\Services\EventTypeManager;


class EventBuilderService
{
    
    protected EventTypeManager $eventTypeManager;

    public function __construct(EventTypeManager $eventTypeManager )
    {
        $this->eventTypeManager = $eventTypeManager;
    }

    
     /**
     * Ajoute les données d'un événement au tableau des événements.
     * @param string $eventType Nom de la CONSTANT appelle dans FcmCommun Enums
     * @return array retour $eventTypes,
     * @return array retour $objectClass,
     * @return array retour $objectUuid, (mettre en dernier uuid qu'on souhaite enregistre)
     * @return array retour $details
     * @param string $uuid (celle qu'on souhaite sauvegarde dans TableEvent)
     * @param array $addtionnalDetails ((automatique date_debut et tache) => ajouter en array d'autres details)
     * @param $record
     * 
     */
    public function addEventData(
        string  $eventType,
        array   &$eventTypes,
        array   &$objectClass,
        array   &$objectUuid,
        array   &$details,
        string  $uuid,
        array $additionalDetails = [],
        $record = null,
        string $prefix = '',
    ): void {
        Log::info('addEventData appele',['eventType'=>$eventType,'uuid'=>$uuid]);
        
        try{
        $detail = $this->eventTypeManager->getEventTypeDetails($eventType,$record);

        $eventTypes[]               = $detail['event_type'];
        $objectClass[$eventType]    = $detail['object_class'];
        $objectUuid[$eventType]     = $uuid;
        $details[$eventType] = array_merge([
            "date_debut"    => Carbon::today()->toDateString(),
            "tache"         => $detail['tache'],
            "event_statut"  => $this->eventTypeManager->statutEvent(0),
            "prefix"        => $prefix,
        ], $additionalDetails);
       
        Log::info('addEventData charge complet ok');

    } catch (\Exception $e){
        Log::error('addEventData echec',['error' => $e->getMessage()]);
        throw $e;
    }
}
}
