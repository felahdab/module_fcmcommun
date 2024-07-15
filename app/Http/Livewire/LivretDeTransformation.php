<?php

namespace Modules\FcmCommun\Http\Livewire;

use Ijpatricio\Mingle\Concerns\InteractsWithMingles;
use Ijpatricio\Mingle\Contracts\HasMingles;
use Livewire\Component;

use App\Models\User;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\UserParcours;
use Modules\FcmCentral\Services\ParcoursService;
use Modules\FcmCommun\DataObjects\ParcoursDto;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LivretDeTransformation extends Component implements HasMingles
{
    use InteractsWithMingles;

    public string $uuid="2bb1800f-c247-434b-abef-4eab4cff0836"; // pour les tests uniquement.

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
        ];
    }

    public function get_livret()
    {
        // On charge un parcours serialise, mais ensuite, cela devra etre le parcours serialise d'un utilisateur.
        $parcours = UserParcours::where("user_id", $this->uuid)->get()->last();
        //dump($parcours);
        $dto = ParcoursDto::from($parcours->parcours);
        //dump($dto);
        $nodes = Arr::keyBy(ParcoursService::transform_for_treeview([$dto->toArray()]), "id" );
        //dump($nodes);

        $roots = ParcoursService::get_roots_for_treeview([$dto->toArray()]);

        $livret = [
                "roots" => $roots,
                "nodes" => $nodes
        ];

        //dump($livret);

        return json_encode($livret);
    }

    public function update_livret($livret)
    {
        $parcours = UserParcours::where("user_id", $this->uuid)->get()->last();
        $dto = ParcoursDto::from($parcours->parcours);
        $nodes = Arr::keyBy(ParcoursService::transform_for_treeview([$dto->toArray()]), "id" );

        $livret_after=Arr::dot($livret);
        //ddd($livret_after);
        $livret_after=Arr::where($livret_after, function ($value, $key) {
            if (Str::contains($key, "checked")) return true;
            return false;
        });
        //dump($livret_after);
        //dump('-----------------------');

        $livret_before = Arr::dot($nodes);
        $livret_before=Arr::where($livret_before, function ($value, $key) {
            if (Str::contains($key, "checked")) return true;
            return false;
        });
        //dump($livret_before);
        //dump('-----------------------');
        //ddd($secondColl);

        $changes=[];
        foreach ($livret_before as $key => $value){
            if ($livret_before[$key] != $livret_after[$key]){
                $changes[$key] = $livret_after[$key] ? "set" : "unset";
            }
        }
        //dump($changes);
        $changes=Arr::undot($changes);
        //ddd($changes);
        
        ParcoursService::applyChanges($parcours, $changes);

        return;
    }

    public function render()
    {
        return view('fcmcommun::livewire.livret-de-transformation');
    }
}
