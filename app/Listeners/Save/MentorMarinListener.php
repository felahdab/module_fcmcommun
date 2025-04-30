<?php

namespace Modules\FcmCommun\Listeners\Save;



use Illuminate\Support\Facades\Log;

use Filament\Notifications\Notification;
use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCommun\Services\EventTypeManager;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Modules\FcmCommun\Enums\Constants;

class MentorMarinListener
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
        

        if (! $event->isOfType(EventTypeManager::ATTRIBUER_MENTOR_MARIN)){
            return;
        }
        
       //dd($event->marin_uuid);

        //Recupere le Marin  et les données
        $marin= $event->record;
        $data = $event->data;

        // Recuperer le marin via son UUID
        // $marin_uuid = $event->marin_uuid;
        // $marin = Marin::where('uuid',  $marin_uuid)->first();
        // Log
        if ($marin) {

            Log::info('Marin trouvé :', ['id' => $marin->id]);

            // FcmCentral
            $fcmMarinClass = $this->getModelClass($event->prefixTable , 'FcmMarin');
            $fcmMarinClass::updateorCreate(
                // Condition pour trouver le marin
                ['rh_marin_id' => $marin->id],
                [
                    'data' => null,
                    'mentor_id' =>   $data['mentor_id'] ?? null,
                ],
            );

            // Retour Log
            Log::info($event->prefixTable . 'fcm_marin  :', [
                'rh_marin_id' => $marin->id,
                'nom' => $marin->nom,
                'uuid' => $marin->uuid,
                'mentor_id' =>   $data['mentor_id'] ?? null,
            ]);
            
            $notification = Constants::getMess(Constants::MENTOR,$marin->nom);

            // Notification Filament
            Notification::make()
                ->title($notification['title'])
                ->success()
                ->body($notification['message'])
                ->send();
        } else {
            Log::warning('Aucun Marin trouvé  UUID :' . $marin->uuid);
        }
    }
}
