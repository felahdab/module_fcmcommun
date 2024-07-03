<?php

namespace Modules\FcmCommun\Http\Livewire;

use Ijpatricio\Mingle\Concerns\InteractsWithMingles;
use Ijpatricio\Mingle\Contracts\HasMingles;
use Livewire\Component;

class LivretDeTransformation extends Component implements HasMingles
{
    use InteractsWithMingles;

    public function component(): string
    {
        // Cette chaine de caractere est la meme que dans l'appel a la methode mingle dans le
        // composant vue. C'est tout ce qui compte.
        // Ca pourrait tout aussi bien etre toto ou zboobie.
        return 'livret-de-transformation.js';
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

    public function render()
    {
        return view('fcmcommun::livewire.livret-de-transformation');
    }
}
