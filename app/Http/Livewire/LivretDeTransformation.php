<?php

namespace Modules\FcmCommun\Http\Livewire;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;

use Filament\Forms\Components\TextInput;

use Ijpatricio\Mingle\Concerns\InteractsWithMingles;
use Ijpatricio\Mingle\Contracts\HasMingles;
use Livewire\Component;

use App\Models\User;
use Illuminate\Contracts\Pipeline\Hub;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\UserParcours;
use Modules\FcmCentral\Services\ParcoursService;
use Modules\FcmCommun\DataObjects\ParcoursDto;

class LivretDeTransformation extends Component implements HasMingles, HasActions, HasForms
{
    use InteractsWithMingles;
    use InteractsWithActions;
    use InteractsWithForms;

    public string $uuid=""; // pour les tests uniquement.

    public function mount(string $uuid = "2bb1800f-c247-434b-abef-4eab4cff0836") {
        $this->uuid = $uuid;
    }

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

    public function updateAction(): Action
    {
        return Action::make('updateAction')
            ->label("Mettre à jour le livret")
            ->requiresConfirmation()
            ->form([
                TextInput::make('commentaire')
            ])
            ->action(function (array $arguments, array $data) {
                $livret = $arguments["livret"];
                $commentaire = $data["commentaire"];
                $this->update_livret($livret, $commentaire);
                return;
            });
    }

    public function triggerUpdateAction(): Action
    {
        return Action::make('triggerUpdateAction')
            ->label("Mettre à jour le livret")
            ->action(function(){
                $this->dispatch('fcm-livretfcm--update');
            });
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

    public function update_livret($livret, $commentaire)
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
