<?php

namespace Modules\FcmCommun\Listeners\Save;

use Modules\FcmCentral\Models\Marin as FcmCentralMarin;
use Modules\FcmUnite\Models\Marin as FcmUniteMarin;
use Modules\RH\Models\Marin as RhMarin;

use Modules\FcmCommun\Events\SaveFcmMarinEvent;
use Illuminate\Support\Facades\Log;

use Filament\Notifications\Notification;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SaveFcmMarinListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Gere l'evenement.
     * 
     * @param SaveFcmMarinEvent $event;
     * @return void
     */
    public function handle(SaveFcmMarinEvent $event): void
    {
        
        //Recupere le Marin  et les données
        $marin = $event->marin;
        $data = $event->data;
        
        // Recuperer le marin via son UUID
        $marin = RhMarin::where('uuid', $event->marin->uuid)->first();
        // Log
        if ($marin){

            Log::info('Marin trouvé :', ['id'=>$marin->id]);
            
            // FcmCentral
            FcmCentralMarin::updateorCreate(
                // Condition pour trouver le marin
                ['rh_marin_id' => $marin->id] ,
                [
                    'data' => null,
                    'cohorte_id' => $data['cohorte_id'] ?? null,
                    'mentor_id' =>   $data['mentor_id'] ?? null,
                ],
            );
            // Retour Log
        Log::info('FcmCentral_marin  :', [
            'rh_marin_id'=>$marin->id,
            'nom'=>$marin->nom,
            'uuid'=>$marin->uuid,
            'cohorte_id' => $data['cohorte_id'] ?? null,
            'mentor_id' =>   $data['mentor_id'] ?? null,
        ]);
            // RH
            
            $existingData = $marin->data ?? [];
            $existingData["fcm"] = ["en_fcm" => true ];
            $marin->data = $existingData;
            $marin->save();
            //dd($marin->data->toArray());
            //dd(json_encode($marin->data));

              // Retour Log
        Log::info('Flag Rh_Marin  true', [
            'nom'=>$marin->nom,
            'uuid'=>$marin->uuid,
        ]);

        // Notification Filament
        Notification::make()
        ->title('Mise à jour du suivi FCM')
        ->success()
        ->body("Le Marin {$marin->nom} est en suivi FCM ")
        ->send();

        }else{
            Log::warning('Aucun Marin trouvé  UUID :'. $marin->uuid);
        }

        
        
        
            
        

    }
}
