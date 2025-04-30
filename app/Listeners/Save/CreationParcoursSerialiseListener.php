<?php

namespace Modules\FcmCommun\Listeners\Save;


use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Modules\FcmCommun\Enums\Constants;
use Modules\FcmCommun\Services\EventTypeManager;
use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;

class CreationParcoursSerialiseListener
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
        

        if (! $event->isOfType(EventTypeManager::CREATION_PARCOURS_SERIALISE)){
            return;
        }

      
        
        $data           = $event->data;
        $parcours       = $event->record;
     

        //dd($data,$parcours);

       
            $date           = $data['date_debut'];
            $version        = $data['version'];
            $libelleCourt   = $data['libelle_court'];
            $libelleLong    = $data['libelle_long'];

         
          

       
            // Sérialiser les données du parcours et ses relations
            $serializedData =  $parcours->toArray();

            //dd( $serializedData);
           
            // Enregistrer dans ParcoursSerialize
            $parcoursSerialiseClass = $this->getModelClass($event->prefixTable , 'ParcoursSerialise');
            $parcoursSerialise = $parcoursSerialiseClass::create([
                'parcours_id'   => $parcours->id,
                'parcours'      => $serializedData,
                'date_debut'    => $date,
                'libelle_court' => $libelleCourt,
                'libelle_long'  => $libelleLong,
                'version'       => $version,
            ]);

            Log::info("Parcours {$libelleCourt} sérialisé à la date {$date}");

            $notification = Constants::getMess(Constants::PARCOURS,$parcours->libelle_court);
            
            // Notification Filament
            Notification::make()
                ->title($notification['title'])
                ->success()
                ->body($notification['message'])
                ->send();

                // Mise à jour de la table Event
            // $storedEventClass = $this->getModelClass($event->prefix, 'StoredEvent');
            // $storedEvent = $storedEventClass::where('event_type', EventTypeService::CREATION_PARCOURS_SERIALISE)
            //     ->orderBy('created_at', 'desc')
            //     ->first();

            // if ($storedEvent) {
            //     $storedEvent->update(['object_uuid' => $parcoursSerialise->uuid]);
            // }

          
        
    }
}

