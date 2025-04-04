<?php

namespace Modules\FcmCommun\Events;

use Modules\FcmCentral\Models\Parcours;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class SerializeParcoursEvent
{
    use  Dispatchable, SerializesModels;

    public $parcours;
    public $data;

    /**
     * Creer un nouvel Event.
     * 
     * @param  Parcours|nulll $parcours a serialiser (ou null pour tous les parcours)
     * @param array $data data annexe a sauvegarder
     * 
     * 
     */
    public function __construct(Parcours $parcours,?array $data=[]  )
    {
        $this->parcours     = $parcours;
        $this->data         = $data;
    
    }

    
}
