<?php

namespace Modules\FcmCommun\Events;

use Modules\Rh\Models\Marin;
use Illuminate\Queue\SerializesModels;

class SuivreMarinFcmEvent
{
    use  SerializesModels;

    public $marin;
    public $data;


    /**
     * Create a new event instance.
     *  @param Marin $marin
     * @param array|null $data
     */
    public function __construct(Marin $marin, ?array $data=[])
    {
        $this->marin = $marin;
        $this->data = $data;

    }


}
