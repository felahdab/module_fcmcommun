<?php

namespace Modules\FcmCommun\Events;


use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\FcmCommun\Models\FcmMarin;
use Modules\FcmCommun\Models\ParcoursSerialise;

class AssignerMarinParcoursEvent
{
    use Dispatchable, SerializesModels;


    public $fcmMarin;
    public $parcoursSerialise;
    public $data;

    /**
     * Creer un nouvel Event.
     * 
     * @param  Parcours|nulll $parcours a serialiser (ou null pour tous les parcours)
     * @param array $data data annexe a sauvegarder
     * 
     * 
     */
    public function __construct(FcmMarin $fcmMarin, ParcoursSerialise $parcoursSerialise,?array $data=[]  )
    {
        $this->fcmMarin             = $fcmMarin;
        $this->parcoursSerialise    = $parcoursSerialise;
        $this->data                 = $data;
    
    }
}
