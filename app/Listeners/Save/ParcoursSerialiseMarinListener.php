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
        $record = $event->record;
        $data = $event->data;
        $prefix = $event->prefixTable;





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

        // Si le record provient de Rh_Marins
        if ($record->tableWithPrefix == 'rh_marins') {
            $marin =   $record;
            // Récupérer le FCM Marin
            $fcmMarinClass = $this->getModelClass($prefix, 'FcmMarin');
            $fcmMarin = $fcmMarinClass::where('rh_marin_id', $marin->id)->first();

            if (!$fcmMarin) {
                Log::warning('FCM Marin introuvable.', ['rh_marin_id' => $marin->id]);
                return;
            }
        } else {
            // Si ca vient de Fcm_Marin
            $fcmMarin = $record;
            $marin = $record->marin;
        }

        // Récupérer les taux et autres données
        $tauxGlobal = $data['taux_global'] ?? 0;
        $tauxStage = $data['taux_stage'] ?? 0;
        $tauxActivite = $data['taux_activite'] ?? 0;
        $parcoursMarin = $data['parcoursmarin'] ?? null;

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

        $notification = Constants::getMess(Constants::MARIN_PARCOURS, $marin->nom);

        // Notification Filament
        Notification::make()
            ->title($notification['title'])
            ->success()
            ->body($notification['message'])
            ->send();
    }
}
