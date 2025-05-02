<?php

namespace Modules\FcmCommun\Listeners\Save;

use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCommun\Services\EventTypeManager;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Modules\FcmCommun\Enums\Constants;

class ParcoursSerialiseMarinListener
{
    use LoadsModelsDynamically;

    /**
     * Gère l'événement.
     *
     * @param UserGeneratedEvent $event
     * @return void
     */
    public function handle(UserGeneratedEvent $event): void
    {
        // Vérifier si l'événement est du bon type
        if (!$event->isOfType(EventTypeManager::ATTRIBUER_PARCOURS_SERIALISE_MARIN)) {
            return;
        }

        // Récupérer le marin et les données
        $record = $event->record; // //$record: FcmCentral\Models\Marin
        $data = $event->data;
        /**
         * array:2 [▼ // vendor/spatie/laravel-ignition/src/helpers.php:14
        * "parcoursserialise_id" => "1"
        *  "marinUuid" => "18195308-274c-11f0-a34a-0242ac190009"
        *]
         */
        $prefix = $event->prefixTable; // "fcmcentral_" 

        // Vérifier si les données nécessaires sont présentes
        if (!isset($data['parcoursserialise_id'])) {
            Log::warning('ID du parcours serialisé manquant.', ['data' => $data]);
            return;
        }

        // Récupérer le parcours serialisé
        $parcoursSerialiseClass = $this->getModelClass($prefix, 'ParcoursSerialise');
        $parcoursSerialise = $parcoursSerialiseClass::find($data['parcoursserialise_id']);

        if (!$parcoursSerialise) {
            Log::warning('Parcours serialisé introuvable.', ['id' => $data['parcoursserialise_id']]);
            return;
        }

        // Extrait du modèle FcmCentral\Models\Marin:
        // * Grace à la définition de cet attribut, le developpeur peut utiliser les méthodes suivantes:
        // * $marin->complements_fcm->cohorte
        // * $marin->complements_fcm->mentor
        // * $marin->complements_fcm->donnes_du_parcours->taux_global
        // * $marin->complements_fcm->donnes_du_parcours->taux_stage
        // * $marin->complements_fcm->donnes_du_parcours->taux_activite
        // * $marin->complements_fcm->donnes_du_parcours->parcours_serialise
        // Si le record provient de Rh_Marins
        
        $fcmMarin = $record->complements_fcm;

        if (!$fcmMarin) {
            Log::warning('FCM Marin introuvable.', ['rh_marin_id' => $record->id]);
            return;
        }

        // Récupérer les taux et autres données
        $tauxGlobal = $data['taux_global'] ?? 0;
        $tauxStage = $data['taux_stage'] ?? 0;
        $tauxActivite = $data['taux_activite'] ?? 0;
        $parcoursMarin = $data['parcoursmarin'] ?? null;

        // Si un parcours sérialisé est déjà attaché, le détacher
        if ($fcmMarin->parcoursSerialises->count())
        {
            $fcmMarin->parcoursSerialises()->detach();
        }

        // Attacher le parcours serialisé au marin
        $fcmMarin->parcoursSerialises()->attach($parcoursSerialise->id, [
            'taux_global' => $tauxGlobal,
            'taux_stage' => $tauxStage,
            'taux_activite' => $tauxActivite,
            'parcoursmarin' => $parcoursMarin,
        ]);

        // Log de l'action
        Log::info('Parcours serialisé attribué au marin.', [
            'fcmmarin_id' => $fcmMarin->id,
            'parcoursserialise_id' => $parcoursSerialise->id,
        ]);

        $notification = Constants::getMess(Constants::MARIN_PARCOURS, $record->nom);

        // Notification Filament
        Notification::make()
            ->title($notification['title'])
            ->success()
            ->body($notification['message'])
            ->send();
    }
}
