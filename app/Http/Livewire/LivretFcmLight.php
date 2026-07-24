<?php
// filepath: Modules/FcmCommun/Http/Livewire/LivretFcmLight.php

namespace Modules\FcmCommun\Http\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\FcmCommun\Http\Livewire\Concerns\LivretFcmLight\HasDisplayHelpers;
use Modules\FcmCommun\Http\Livewire\Concerns\LivretFcmLight\HasFiltres;
use Modules\FcmCommun\Http\Livewire\Concerns\LivretFcmLight\HasParcoursData;
use Modules\FcmCommun\Http\Livewire\Concerns\LivretFcmLight\HasPermissions;
use Modules\FcmCommun\Http\Livewire\Concerns\LivretFcmLight\HasPieceJointe;
use Modules\FcmCommun\Http\Livewire\Concerns\LivretFcmLight\HasValidationActions;

#TODO: vérifier les dépendances avec FcmCentral et FcmUnite

class LivretFcmLight extends Component
{
    use WithFileUploads;
    use HasPermissions;
    use HasParcoursData;
    use HasFiltres;
    use HasPieceJointe;
    use HasValidationActions;
    use HasDisplayHelpers;

    #[Locked]
    public $marinId;

    #[Locked]
    public $Marin;

    #[Locked]
    public $UserGeneratedEvent;

    #[Locked]
    public $PermissionPrefix;

    // ========================================
    // INIT
    // ========================================

    public function mount()
    {
    }

    public function boot()
    {
    }

    // ========================================
    // ÉVÉNEMENTS ET SYNCHRONISATION
    // ========================================

    public function refreshElements()
    {
        $this->dispatch('elements-refreshed');
    }

    #[On('sync-elements')]
    public function syncElements()
    {
        $this->refreshElements();
    }

    public function afterElementAction()
    {
        usleep(200000); // 200ms
        $this->dispatch('elements-refreshed');
    }

    // ========================================
    // RENDU
    // ========================================

    public function render()
    {
        return view('fcmcommun::livewire.livretfcmlight.livretfcmlight', [
            'marin' => $this->marin,
            'parcours' => $this->parcoursFiltre,
            'isCurrentUser' => $this->isCurrentUser,
            'savoirFaireChildren' => $this->savoirFaireChildren,
            'objectifsParSavoirfaire' => $this->objectifsParSavoirfaire,
            'statistiques' => $this->statistiques,
            'affectationsDisponibles' => $this->affectationsDisponibles,
            'competencesDisponibles' => $this->competencesDisponibles,
            'savoirfaireTypesDisponibles' => $this->savoirfaireTypesDisponibles,
            'hasActiveFilters' => $this->hasActiveFilters,
            'savoirfairesAGriser' => $this->savoirfairesAGriser,
            'objectifsAGriser' => $this->objectifsAGriser,
            'marinEstGele' => $this->marinEstGele,
            'marinEstEnFcm' => $this->marinEstEnFcm,

        ]);
    }
}
