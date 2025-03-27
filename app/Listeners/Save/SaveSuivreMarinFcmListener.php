<?php

namespace Modules\FcmCommun\Listeners\Save;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Modules\FcmCommun\Events\SuivreMarinFcmEvent;
use Illuminate\Support\Facades\Log;
use Modules\RH\Models\Marin as RhMarin;

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
        
        $existingData["fcm"] = ["en_fcm" => true ];
        $marin->data = $existingData;
        $marin->save();
        
        }else{
            Log::warning('Aucun Marin trouvé  UUID :'. $marin->uuid);
        }

        
    }
}
