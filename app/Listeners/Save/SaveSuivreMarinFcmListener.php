<?php

namespace Modules\FcmCommun\Listeners\Save;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Modules\FcmCommun\Events\SuivreMarinFcmEvent;
use Illuminate\Support\Facades\Log;
use Modules\RH\Models\Marin as RhMarin;
use Filament\Notifications\Notification;

class SaveSuivreMarinFcmListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     * @param SuivreMarinFcmEvent $event;
     * @return void
     */
    public function handle(SuivreMarinFcmEvent $event): void
    {
        //Recupere le Marin  et les données
        $marin = $event->marin;
        $data = $event->data;
        
        // Recuperer le marin via son UUID
        $marin = RhMarin::where('uuid', $event->marin->uuid)->first();
        // Log
        if ($marin){
            Log::info('Marin trouvé :', ['id'=>$marin->id]);

            // RH
        
        $existingData = $marin->data ?? [];
        if (isset($data['fcm']['en_fcm'])){
            $existingData["fcm"] = ["en_fcm" => $data['fcm']['en_fcm'] ];
        }else{
            Log::error('Clé "fcm.en_fcm" manquant');
        }
        
        $marin->data = $existingData;
        $marin->save();

        // Retour Log
        Log::info('Marin clé à jour :', [
            'id'=>$marin->id,
            'nom'=>$marin->nom,
            'uuid'=>$marin->uuid,
            'data'=>$marin->data,
        ]);

        // Notification FIlament
        $message = isset($data['fcm']['en_fcm']) && $data['fcm']['en_fcm']
            ? 'est maintenant suivi en FCM'
            : 'ne suit plus la FCM';

        Notification::make()
        ->title('Mise à jour du suivi FCM')
        ->success()
        ->body("Le Marin {$marin->nom} {$message}")
        ->send();

        }else{
            Log::warning('Aucun Marin trouvé  UUID :'. $marin->uuid);
        }

        
    }
}
