<?php

namespace Modules\FcmCommun\Listeners\Save;


use Modules\FcmCommun\Models\Marin as Marin;


use Illuminate\Support\Facades\Log;

use Filament\Notifications\Notification;
use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCommun\Services\EventTypeManager;
use Modules\FcmCommun\Enums\Constants;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;

class FcmMarinListener
{

    use LoadsModelsDynamically;

    /**
     * Gere l'evenement.
     * 
     * @param UserGeneratedEvent $event;
     * @return void
     */
    public function handle(UserGeneratedEvent $event): void
    {


        if (
            !$event->isOfType(EventTypeManager::MARIN_EN_FCM) &&
            !$event->isOfType(EventTypeManager::MARIN_PAS_FCM)
        ) {
            return;
        }





        // Récupérer le marin et les données
        $marin = $event->record;
        $data = $event->data;

        if ($marin) {
            Log::info('Marin trouvé :', ['id' => $marin->id]);

            // Vérifier et mettre à jour le champ "en_fcm"
            $existingData = $marin->data ?? [];
            $isInFcm = $existingData['fcm']['en_fcm'] ?? false;

            if ($isInFcm) {

                // Si déjà en FCM, inverser l'état
                $existingData['fcm']['en_fcm'] = false;

                // Sauvegarder les données mises à jour
                $marin->data = $existingData;
                $marin->save();

                // Log de l'action
                Log::info('Le marin a été retiré du suivi FCM', [
                    'nom' => $marin->nom,
                    'uuid' => $marin->uuid,
                ]);

                $notification = Constants::getMess(Constants::MARIN_PAS_FCM, $marin->nom);

                // Notification
                Notification::make()
                    ->title($notification['title'])
                    ->success()
                    ->body($notification['message'])
                    ->send();


            } else {

                
                // Si pas encore en FCM, activer le suivi
                $existingData['fcm']['en_fcm'] = true;

                // Sauvegarder les données mises à jour
                $marin->data = $existingData;
                $marin->save();

                // Log de l'action
                Log::info('Le marin a été ajouté au suivi FCM', [
                    'nom' => $marin->nom,
                    'uuid' => $marin->uuid,
                ]);

                $notification = Constants::getMess(Constants::MARIN_EN_FCM, $marin->nom);

                // Notification
                Notification::make()
                    ->title($notification['title'])
                    ->success()
                    ->body($notification['message'])
                    ->send();

                // FcmCentral
                $fcmMarinClass = $this->getModelClass($event->prefixTable, 'FcmMarin');
                //$fcmMarin =$marinClass::find($cohorte_id);
                $fcmMarinClass::updateorCreate(
                    // Condition pour trouver le marin
                    ['rh_marin_id' => $marin->id],
                    [
                        //'data' => null,
                        //'cohorte_id' => $data['cohorte_id'] ?? null,
                        //'mentor_id' =>   $data['mentor_id'] ?? null,
                    ],
                );

                // Retour Log
                Log::info($marin->prefixTable . 'fcm_marin  :', [
                    'rh_marin_id' => $marin->id,
                    'nom' => $marin->nom,
                    'uuid' => $marin->uuid,
                    //'cohorte_id' => $data['cohorte_id'] ?? null,
                    //'mentor_id' =>   $data['mentor_id'] ?? null,
                ]);
            }
        } else {
            Log::warning('Aucun Marin trouvé avec UUID : ' . $event->marin_uuid);
        }
    }
}
