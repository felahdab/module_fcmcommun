<?php

namespace Modules\FcmCommun\Listeners\Save;

use Modules\FcmCommun\Events\AssignerMarinParcoursEvent;
use Modules\FcmUnite\Models\FcmMarin as FcmUniteMarin;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class SaveAssignerMarinParcoursListener
{
    public $fcmmarinclass;
    

    /**
     * Handle the event.
     *
     * @param AssignerMarinParcourEvent $event
     * @return void
     */
    public function handle(AssignerMarinParcoursEvent $event)
    {


        // Recupere les datas
        $data                   = $event->data;
        $parcoursSerialise      = $event->parcoursSerialise;
        $fcmMarin               = $event->fcmMarin;


        $tauxGlobal         = $data['taux_global'] ?? 0;
        $tauxStage          = $data['taux_stage'] ?? 0;
        $tauxActivite       = $data['taux_activite'] ?? 0;
        $parcoursMarin      = $data['parcoursmarin'] ?? null;

        $fcmMarin->parcoursSerialises()->attach($parcoursSerialise->id, [
            'taux_global'       => $tauxGlobal,
            'taux_stage'        => $tauxStage,
            'taux_activite'     => $tauxActivite,
            'parcoursmarin'     => $parcoursMarin ,
        ]);

        // Retour Log
        Log::info('Parcours Serialie / Marin :', [
            'fcmmarin_id' => $fcmMarin->id,
            'parcoursserialise_id' => $parcoursSerialise->id
        ]);

        // Notification Filament
        Notification::make()
            ->title('Mise à jour Marin / Parcours')
            ->success()
            ->body("Le Parcours {$parcoursSerialise->libelle_court} Version {$parcoursSerialise->version}  a été atribué au Marin {$fcmMarin->marin->nom} ")
            ->send();
    }
}
