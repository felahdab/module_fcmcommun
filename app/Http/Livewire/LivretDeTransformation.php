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

    public function get_livret()
    {
        $livret = [
            "roots" => ["id1", "id2", "id3", "id4"],
            "nodes" => [
                "id1" => [
                    "text"=> "text1",
                    "children" => ["id11"],
                    "state" => []
                ],
                "id11" => [
                    "text"=> "text11",
                    "children" => [],
                    "state" => []
                ],
                "id2" => [
                    "text"=> "text2 from server",
                    "children" => [],
                    "state" => []
                ],
                "id3" => [
                    "text"=> "text3 from server",
                    "children" => [],
                    "state" => []
                ],
                "id4" => [
                    "text"=> "text4 from server",
                    "children" => ["id41"],
                    "state" => []
                ],
                "id41" => [
                    "text"=> "text41 from server",
                    "children" => [],
                    "state" => []
                ],
            ]
        ];
      
      //ddd($livret);
        return json_encode($livret);
    }

    public function render()
    {
        return view('fcmcommun::livewire.livret-de-transformation');
    }
}
