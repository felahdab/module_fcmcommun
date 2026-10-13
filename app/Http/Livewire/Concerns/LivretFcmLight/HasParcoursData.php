<?php

namespace Modules\FcmCommun\Http\Livewire\Concerns\LivretFcmLight;

use Livewire\Attributes\Computed;

trait HasParcoursData
{
    #[Computed]
    public function marin()
    {
        return $this->Marin::select(['id', 'uuid', 'nid', 'nom', 'prenom'])
            ->with(['complementsFcm:id,rh_marin_id'])
            ->find($this->marinId);
    }

    #[Computed]
    public function parcours()
    {
        if (!$this->marin->complements_fcm?->donnees_du_parcours?->parcoursmarin) {
            return null;
        }

        return $this->marin->complements_fcm->donnees_du_parcours->parcoursmarin;
    }

    #[Computed]
    public function parcoursFiltre()
    {
        if (!$this->parcours) {
            return null;
        }

        return $this->appliquerFiltres($this->parcours);
    }

    #[Computed]
    public function isCurrentUser()
    {
        return $this->marin->isCurrentUser();
    }
}
