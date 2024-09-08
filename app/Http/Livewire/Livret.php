<?php

namespace Modules\FcmCommun\Http\Livewire;

use Livewire\Component;
use Filament\Actions\Action;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

use Filament\Forms\Components\Textarea;

use Modules\FcmCentral\Services\ParcoursService;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\UserParcours;

use Modules\FcmCommun\Services\LivretService;

class Livret extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public $uuid;
    public $livret;

    public function mount($uuid)
    {
        $this->uuid = $uuid;
        $this->livret = LivretService::get_livret($this->uuid);
    }

    public function render()
    {
        return view('fcmcommun::livewire.livret');
    }

    public function updateAction(): Action
    {
        return Action::make('updateAction')
            ->label("Mettre à jour le livret")
            ->visible($this->authorizeUser())
            ->requiresConfirmation()
            ->form([
                Textarea::make('commentaire')
            ])
            ->action(function (array $arguments, array $data) {
                $livret = $this->livret['nodes'];
                $commentaire = $data["commentaire"];
                LivretService::update_livret($this->uuid, $livret, $commentaire);
                $this->dispatch('$refresh');
                return;
            });
    }

    public function toggleNode($nodeid)
    {
        if (! array_key_exists('opened', $this->livret['nodes'][$nodeid]['state'])){
            $this->livret['nodes'][$nodeid]['state']['opened']=false;
        }
        $this->livret['nodes'][$nodeid]['state']['opened'] = ! $this->livret['nodes'][$nodeid]['state']['opened'];
    }

    public function authorizeUser(){
        return true;
    }
}
