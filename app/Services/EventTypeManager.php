<?php

namespace Modules\FcmCommun\Services;

class EventTypeManager
{
    
    
    public const ATTRIBUER_COHORTE_MARIN                 = 'attribuer_cohorte_marin';
    public const MARIN_EN_FCM                            = 'marin_en_fcm';
    public const MARIN_PAS_FCM                           = 'marin_pas_fcm';
    public const ATTRIBUER_MENTOR_MARIN                  = 'attribuer_mentor_marin';
    public const ATTRIBUER_PARCOURS_SERIALISE_MARIN      = 'attribuer_parcours_serialise_marin';
    public const CREATION_PARCOURS_SERIALISE             = 'creation_parcours_serialise';


    // Tableau des Statuts des Evenements
    public const STATUTS_EVENT = [
        0 => 'A Importer',
        1 => 'Importation en Attente',
        2 => 'Importation OK',
        3 => 'Erreur Importation',
    ];

    
    /**
     * Retourne tous les types d'événements avec leurs métadonnées.
     */
    public function getAllEventTypes($record = null): array
    {
        return [
            self::ATTRIBUER_COHORTE_MARIN => [
                'listener'      => 'Modules\FcmCommun\Listeners\Save\CohorteMarinListener',
                'event_type'    => self::ATTRIBUER_COHORTE_MARIN,
                'object_class'  => 'Cohorte',
                'tache'         => $record
                ? " Attribuer une Cohorte a {$record->nom} {$record->prenom}  "
                : ' Attribuer une Cohorte a un Marin '
            ],
    
            self::MARIN_EN_FCM => [
                'listener'      => 'Modules\FcmCommun\Listeners\Save\FcmMarinListener',
                'event_type'    => self::MARIN_EN_FCM,
                'object_class'  => 'Marin',
                'tache'         => $record
                ? " Le Marin  {$record->nom} {$record->prenom} est en FCM  "
                : ' Un Marin est en FCM '
            ],

            self::MARIN_PAS_FCM => [
                'listener'      => 'Modules\FcmCommun\Listeners\Save\FcmMarinListener',
                'event_type'    => self::MARIN_PAS_FCM,
                'object_class'  => 'Marin',
                'tache'         => $record
                ? " Le Marin {$record->nom} {$record->prenom} n'est plus en FCM  "
                : ' Un Marin n\'est plus en FCM '
            ],
    
            self::ATTRIBUER_MENTOR_MARIN => [
                'listener'      => 'Modules\FcmCommun\Listeners\Save\MentorMarinListener',
                'event_type'    => self::ATTRIBUER_MENTOR_MARIN,
                'object_class'  => 'FcmMarin',
                'tache'         => $record
                ? " Attribuer un mentor a  {$record->nom} {$record->prenom}   "
                : ' Attribuer un mentor a un Marin  en FCM '
            ],

            self::CREATION_PARCOURS_SERIALISE => [
                'listener'      => 'Modules\FcmCommun\Listeners\Save\CreationParcoursSerialiseListener',
                'event_type'    => self::CREATION_PARCOURS_SERIALISE,
                'object_class'  => 'ParcoursSerialise',
                'tache'         => " Creation d'une nouvelle Version d'un Parcours   ",
                
            ],
    
            self::ATTRIBUER_PARCOURS_SERIALISE_MARIN => [
                'listener'      => 'Modules\FcmCommun\Listeners\Save\ParcoursSerialiseMarinListener',
                'event_type'    => self::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
                'object_class'  => 'MarinParcours',
                'tache'         => $record
                ? " Attribuer un Parcours a  {$record->nom} {$record->prenom}   "
                : ' Attribuer un Parcours a un Marin  en FCM '
            ],
        ];
    }

    /**
     * Retourne les détails pour un type d'événement spécifique.
     */
    public function getEventTypeDetails(string $eventType, $record = null): ?array
    {
        $eventTypes = $this->getAllEventTypes($record);
        return $eventTypes[$eventType] ?? null;
    }

    /**
     * Vérifie si un type d'événement existe.
     */
    public function eventTypeExists(string $eventType): bool
    {
        $eventTypes = $this->getAllEventTypes();
        return isset($eventTypes[$eventType]);
    }


    /**
     * Retourne le libellé du Statut d'un événement.
     * @param int $statut 0 Import, 1 Attente, 2 OK, 3 Erreur
     */
    public function statutEvent(int $statut): string
    {
        return self::STATUTS_EVENT[$statut] ?? 'Statut Inconnu';
    }
}

