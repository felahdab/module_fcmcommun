<?php

namespace Modules\FcmCommun\Http\Livewire;

use Ijpatricio\Mingle\Concerns\InteractsWithMingles;
use Ijpatricio\Mingle\Contracts\HasMingles;
use Livewire\Component;

class TestMingle extends Component implements HasMingles
{
    use InteractsWithMingles;

    public function component(): string
    {
        return 'Modules/FcmCommun/resources/assets/js/TestMingle/index.js';
    }

    public function mingleData()
    {
        return [    
            'message' => 'Message in a bottle 🍾',
        ];
    }

    public function doubleIt($amount)
    {
        return $amount * 2;
    }
}
