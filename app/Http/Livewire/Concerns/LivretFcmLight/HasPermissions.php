<?php

namespace Modules\FcmCommun\Http\Livewire\Concerns\LivretFcmLight;

use Livewire\Attributes\Computed;

trait HasPermissions
{
    #[Computed]
    public function userEstLeMarin(): bool
    {
        $currentMarin = ($this->Marin)::fromCurrentUser();
        if ($currentMarin == null) return false;

        if ($currentMarin->id == $this->marinId) return true;

        return false;
    }

    // GEL OU SEDIMENTATION PARCOURS
    #[Computed]
    public function marinEstGele(): bool
    {
        return $this->marin->estGele();
    }

    // ✅ SÉDIMENTATION (période initiale avant FCM)
    #[Computed]
    public function marinEstEnSedimentation(): bool
    {
        $dateSedimentation = $this->marin->complements_fcm?->date_sedimentation;
        $dateFcm = $this->marin->complements_fcm?->date_fcm;

        // En sédimentation = date_sedimentation renseignée ET date_fcm NON renseignée
        return $dateSedimentation !== null && $dateFcm === null;
    }

    // ✅ FCM ACTIF (après sédimentation, avant compétence
    #[Computed]
    public function marinEstEnFcm(): bool
    {
        $dateSedimentation = $this->marin->complements_fcm?->date_sedimentation;
        $dateFcm = $this->marin->complements_fcm?->date_fcm;
        $dateCompetence = $this->marin->complements_fcm?->date_competence;

        // ✅ En FCM = date_sedimentation ET date_fcm renseignées ET date_competence NULL
        return $dateSedimentation !== null
            && $dateFcm !== null
            && $dateCompetence === null; // ← OBLIGATOIREMENT NULL pour valider
    }

    // ✅ PEUT PROPOSER/VALIDER = En FCM ET Pas gelé
    #[Computed]
    public function marinPeutProposerOuValider(): bool
    {
        // Si date_competence renseignée → marinEstEnFcm() = false → return false
        return $this->marinEstEnFcm() && !$this->marinEstGele();
    }

    #[Computed]
    public function canProposer()
    {
        if (!$this->marinPeutProposerOuValider()) {
            return false;
        }

        // ✅ Le marin peut toujours proposer ses propres éléments sans permission explicite
        if ($this->userEstLeMarin()) {
            return true;
        }

        return auth()->user()->can($this->PermissionPrefix . '::livret.proposer');
    }

    #[Computed]
    public function canAnnuler()
    {
        if (!$this->marinPeutProposerOuValider()) {
            return false;
        }

        // ✅ Le marin peut toujours annuler ses propres propositions sans permission explicite
        if ($this->userEstLeMarin()) {
            return true;
        }

        return auth()->user()->can($this->PermissionPrefix . '::livret.annuler');
    }

    #[Computed]
    public function canValider()
    {
        if (!$this->marinPeutProposerOuValider()) {
            return false;
        }

        // ✅ Le mentor peut toujours valider sans permission explicite
        $currentMarin = ($this->Marin)::fromCurrentUser();
        if ($currentMarin && $currentMarin->est_mentor_de($this->marin)) {
            return true;
        }

        return auth()->user()->can($this->PermissionPrefix . '::livret.valider');
    }

    #[Computed]
    public function canDevalider()
    {
        return auth()->user()->can($this->PermissionPrefix . '::livret.annuler');
    }

    // ✅ Gestion (ajout/suppression) de la pièce jointe d'un élément déjà validé
    #[Computed]
    public function canGererPieceJointe()
    {
        $currentMarin = ($this->Marin)::fromCurrentUser();
        if ($currentMarin && $currentMarin->est_mentor_de($this->marin)) {
            return true;
        }

        return auth()->user()->can($this->PermissionPrefix . '::livret.valider');
    }

    #[Computed]
    public function canRefuser()
    {
        if (!$this->marinPeutProposerOuValider()) {
            return false;
        }

        // ✅ Le mentor peut toujours refuser sans permission explicite
        $currentMarin = ($this->Marin)::fromCurrentUser();
        if ($currentMarin && $currentMarin->est_mentor_de($this->marin)) {
            return true;
        }

        return auth()->user()->can($this->PermissionPrefix . '::livret.refuser');
    }
}
