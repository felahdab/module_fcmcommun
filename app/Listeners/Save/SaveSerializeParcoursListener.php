<?php

namespace Modules\FcmCommun\Listeners\Save;

use Modules\FcmCommun\Events\SerializeParcoursEvent;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class SaveSerializeParcoursListener
{
    /**
     * Handle the event.
     *
     * @param SerializeParcoursEvent $event
     * @return void
     */
    public function handle(SerializeParcoursEvent $event)
    {
        
        $data           = $event->data;
        $parcours       = $event->parcours;

        $this->serializeParcours($parcours, $data);
       
    }

    /**
     * Serialiser un parcours , enregistrement dans ParcoursSerialise.
     *
     * @param Parcours $parcours
     * @param array $data
     * @return void
     */
    protected function serializeParcours(Parcours $parcours,array $data)
    {
            $date           = $data['date_debut'];
            $version        = $data['version'];
            $libelleCourt   = $data['libelle_court'];
            $libelleLong    = $data['libelle_long'];

         
          

       
            // Sérialiser les données du parcours et ses relations
            $serializedData =  $parcours->toArray();

            //dd( $serializedData);
           
            // Enregistrer dans ParcoursSerialize
            ParcoursSerialise::create([
                'parcours_id'   => $parcours->id,
                'parcours'      => $serializedData,
                'date_debut'    => $date,
                'libelle_court' => $libelleCourt,
                'libelle_long'  => $libelleLong,
                'version'       => $version,
            ]);

            Log::info("Parcours {$libelleCourt} sérialisé à la date {$date}");

            Notification::make()
        ->title('Parcours FCM')
        ->success()
        ->body("Le Parcours {$libelleCourt} a été serialisé  le {$date}")
        ->send();
        
    }
}

