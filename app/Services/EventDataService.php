<?php

namespace Modules\FcmCommun\Services;


use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Modules\FcmCommun\Services\EventBuilderService;
use Modules\FcmCommun\Services\EventTypeManager;
use Illuminate\Support\Facades\Log;

class EventDataService
{

    use LoadsModelsDynamically;

    protected EventBuilderService $eventBuilder;
    protected string $prefix;

    public function __construct(EventBuilderService $eventBuilder, string $prefix)
    {
        $this->eventBuilder = $eventBuilder;
        $this->prefix = $prefix;
    }

    /**
     * Génère les données des événements.
     */
    public function buildEventData(array $data, $record): array
    {

        Log::info('buildEventData appele',['data' =>$data,'record' =>$record]);

        $eventTypes     = [];
        $details        = [];
        $userUuid       = auth()->user()->uuid;
        $marinUuid      = $data['marinUuid'];
        $objectClass    = [];
        $objectUuid     = [];


        // Attribuer une cohorte
        $this->handleCohorte($data, $record, $eventTypes, $objectClass, $objectUuid, $details);

        // Attribuer un mentor
        $this->handleMentor($data, $record, $eventTypes, $objectClass, $objectUuid, $details);

        // Attribuer un parcours serialisé 
        $this->handleParcoursSerialiseMarin($data, $record, $eventTypes, $objectClass, $objectUuid, $details);

        // Creation d'un parcours serialisé
        $this->handleCreationParcoursSerialise($data, $record, $eventTypes, $objectClass, $objectUuid, $details);

        // Gérer le Marin en FCM
        $this->handleMarinFcm($data, $record, $eventTypes, $objectClass, $objectUuid, $details);

        // Gestion Marin Parcours 
        $this->handleUpdateMarinParcours($data, $record, $eventTypes, $objectClass, $objectUuid, $details);

        if ( $eventTypes     != []){
        Log::info('Donnees generes',[
            'eventTypes'    => $eventTypes,
            'details'       => $details,
            'objectClass'   => $objectClass,
            'objectUuid'    => $objectUuid,
            'userUuid'      => $userUuid,
            'marinUuid'     => $marinUuid,

        ]);
    }else{
        Log::warning('Donnees NON generes');
    }

        // Retourner les données nécessaires pour l'événement
        return [
            'eventTypes'    => $eventTypes,
            'details'       => $details,
            'objectClass'   => $objectClass,
            'objectUuid'    => $objectUuid,
            'userUuid'      => $userUuid,
            'marinUuid'     => $marinUuid,

        ];
    }


    protected function handleCohorte(array $data, $record, array &$eventTypes, array &$objectClass, array &$objectUuid, array &$details): void
    {
        if (isset($data['cohorte_id'])) {
            $cohorte = Cohorte::find($data['cohorte_id']);
            if ($cohorte) {
                $this->eventBuilder->addEventData(
                    EventTypeManager::ATTRIBUER_COHORTE_MARIN,
                    $eventTypes,
                    $objectClass,
                    $objectUuid,
                    $details,
                    $cohorte->uuid,
                    [
                        "cohorte_uuid" => $cohorte->uuid,
                        "cohorte_libelle_court" => $cohorte->libelle_court,
                    ],
                    $record,
                    $this->prefix,
                );
            }
        }
    }


    protected function handleMentor(array $data, $record, array &$eventTypes, array &$objectClass, array &$objectUuid, array &$details): void
    {
        if (isset($data['mentor_id'])) {

            $marinClass = $this->getModelClass($this->prefix, 'Marin');
            $mentor = $marinClass::find($data['mentor_id']);
            if ($mentor) {
                $this->eventBuilder->addEventData(
                    EventTypeManager::ATTRIBUER_MENTOR_MARIN,
                    $eventTypes,
                    $objectClass,
                    $objectUuid,
                    $details,
                    $mentor->uuid,
                    [
                        "mentor_uuid" => $mentor->uuid,
                        "mentor_nom" => $mentor->nom,
                    ],
                    $record,
                    $this->prefix,
                );
            }
        }
        Log::info('EventBuilder Data handleMentor genere');
    }


    protected function handleParcoursSerialiseMarin(array $data, $record, array &$eventTypes, array &$objectClass, array &$objectUuid, array &$details): void
    {
        if (isset($data['parcoursserialise_id'])) {

            $parcoursSerialiseClass = $this->getModelClass($this->prefix, 'ParcoursSerialise');
            $parcoursSerialise = $parcoursSerialiseClass::find($data['parcoursserialise_id']);
            if ($parcoursSerialise) {
                $this->eventBuilder->addEventData(
                    EventTypeManager::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
                    $eventTypes,
                    $objectClass,
                    $objectUuid,
                    $details,
                    $parcoursSerialise->uuid,
                    [
                        "parcours_serialise_uuid" => $parcoursSerialise->uuid,
                        "parcours_serialise_nom" => $parcoursSerialise->libelle_court,
                    ],
                    $record,
                    $this->prefix,
                );
            }
        }
    }

    protected function handleCreationParcoursSerialise(array $data, $record, array &$eventTypes, array &$objectClass, array &$objectUuid, array &$details): void
    {
        if (isset($data['libelle_court'],$data['version'])) {

           



                $this->eventBuilder->addEventData(
                    EventTypeManager::CREATION_PARCOURS_SERIALISE,
                    $eventTypes,
                    $objectClass,
                    $objectUuid,
                    $details,
                    $data['version'],
                    [
                        "parcours_serialise_version" => $data['version'],
                        "parcours_serialise_nom" => $data['libelle_court'],
                    ],
                    $record,
                    $this->prefix,
                );
            
        }
    }


    protected function handleUpdateMarinParcours(array $data, $record, array &$eventTypes, array &$objectClass, array &$objectUuid, array &$details): void
    {
        if (isset($data['selectedItems'])) {

           switch($data['eventName']){
            case 'valid' : 
                $eventName =  EventTypeManager::VALID_ELMT_MARIN_PARCOURS; 
                $selectedKey = 'sauve'; 
            break;
            case 'annul' : 
                $eventName =  EventTypeManager::ANNUL_ELMT_MARIN_PARCOURS; 
                $selectedKey = 'annul';
            break;
            case 'propos' : 
                $eventName =  EventTypeManager::PROPOS_ELMT_MARIN_PARCOURS; 
                $selectedKey = 'propos';
            break;
            case 'annulPropos' : 
                $eventName =  EventTypeManager::ANNUL_PROPOS_ELMT_MARIN_PARCOURS;
                $selectedKey = 'aValiderPropos';
            break;
            case 'validPropos' : 
                $eventName =  EventTypeManager::VALID_PROPOS_ELMT_MARIN_PARCOURS;
                $selectedKey = 'aValiderPropos';
            break;
            case 'restore' : 
                $eventName =  EventTypeManager::RESTORE_ELMT_MARIN_PARCOURS;
                $selectedKey = 'restore';
            break;
            case 'reboot' : 
                $eventName =  EventTypeManager::REBOOT_ELMT_MARIN_PARCOURS;
                $selectedKey = 'reboot';
            break;
            default :
                $selectedKey = 'save';
            break;

           }

           $objectUuid = [$data['objectUuid']];



                $this->eventBuilder->addEventData(
                    $eventName,
                    $eventTypes,
                    $objectClass,
                    $objectUuid,
                    $details,
                    $data['objectUuid'],
                    [
                        "init" => $data['selectedItems']['init'] ?? [],
                        $selectedKey => $data['selectedItems'][$selectedKey] ?? [],
                        "commentaire" => $data['commentaire'] ?? '',
                    ],
                    $record,
                    $this->prefix,
                );
            
        }
    }


    protected function handleMarinFcm(array $data, $record, array &$eventTypes, array &$objectClass, array &$objectUuid, array &$details): void
    {
        if (isset($data['fcm'])) {
            $eventTypeService=(($data['fcm']['en_fcm']=='false')? EventTypeManager::MARIN_EN_FCM: EventTypeManager::MARIN_PAS_FCM);
            
            if ($record) {
                $this->eventBuilder->addEventData(
                    $eventTypeService,
                    $eventTypes,
                    $objectClass,
                    $objectUuid,
                    $details,
                    $record->uuid,
                    [
                        "marin_uuid" => $record->uuid,
                        "en_fcm" => (($data['fcm']['en_fcm']=='false')?'OUI':'NON'),
                    ],
                    $record,
                    $this->prefix,
                );
            }
        }
    }
}
