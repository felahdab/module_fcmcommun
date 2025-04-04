<?php

namespace Modules\FcmCommun\Events;

use Modules\FcmCommun\Models\Marin;
use Illuminate\Queue\SerializesModels;

class SaveFcmMarinEvent
{
    use  SerializesModels;

    public $marin;
    public $data;
    public $cohorteId;
    public $mentorId;

    /**
     * Create a new event instance.
     * 
     * @param Marin $marin
     * @param array|null $data
     * 
     */
    public function __construct(Marin $marin, ?array $data=[])
    {
        $this->marin = $marin;
        $this->data = $data;

    }

    
}
