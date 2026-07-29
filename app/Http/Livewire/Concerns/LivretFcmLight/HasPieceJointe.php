<?php
namespace Modules\FcmCommun\Http\Livewire\Concerns\LivretFcmLight;

use Carbon\Carbon;
use Filament\Notifications\Notification;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

trait HasPieceJointe
{
    public $pieceJointe = null;

    // ✅ Ajout/suppression de pièce jointe sur un élément déjà validé (depuis la modale Infos)
    public ?string $pieceJointeGestionType = null;
    public ?string $pieceJointeGestionUuid = null;
    public $nouvellePieceJointeElement = null;

    public function updatedPieceJointe(): void
    {
        $this->validate([
            'pieceJointe' => ['nullable', 'file', 'mimes:pdf', 'max:5120'], // 5 Mo, PDF uniquement
        ], [
            'pieceJointe.mimes' => 'La pièce jointe doit être un fichier PDF.',
            'pieceJointe.max' => 'La pièce jointe ne doit pas dépasser 5 Mo.',
        ]);
    }

    /**
     * Stocke la pièce jointe uploadée (diplôme, justificatif) lors d'une validation
     * et retourne son chemin de stockage, ou null si aucun fichier n'a été fourni.
     *
     * Classement : fcmcommun/livretfcm/pieces-jointes/{savoirfaire|objectif}/{annee}/{mois}
     * pour éviter d'accumuler des milliers de fichiers dans un seul dossier.
     */
    private function stockerPieceJointe(array $elements_a_valider = [], ?Carbon $date = null): ?string
    {
        if (!$this->pieceJointe) {
            return null;
        }

        $this->validate([
            'pieceJointe' => ['file', 'mimes:pdf', 'max:5120'], // 5 Mo, PDF uniquement
        ], [
            'pieceJointe.mimes' => 'La pièce jointe doit être un fichier PDF.',
            'pieceJointe.max' => 'La pièce jointe ne doit pas dépasser 5 Mo.',
        ]);

        $date = $date ?? Carbon::now();
        $categorie = array_key_exists('savoirfaire', $elements_a_valider) ? 'savoirfaire' : 'objectif';

        $directory = sprintf(
            'fcmcentral/livretfcm/pieces-jointes/%s/%s/%s/%s',
            $categorie,
            $this->marin->uuid,
            $date->format('Y'),
            $date->format('m')
        );

        return $this->pieceJointe->store($directory, 's3-private');
    }

    // ========================================
    // GESTION APRÈS VALIDATION (depuis la modale Infos)
    // ========================================

    public function ouvrirGestionPieceJointe(string $type, string $uuid): void
    {
        if (!$this->canGererPieceJointe()) {
            Notification::make()->title('Accès refusé')->warning()->send();

            return;
        }

        if (!$this->elementEstValide($type, $uuid)) {
            Notification::make()
                ->title('Action impossible')
                ->body('La pièce jointe ne peut être ajoutée que sur un élément validé.')
                ->warning()
                ->send()
            ;

            return;
        }

        $this->pieceJointeGestionType = $type;
        $this->pieceJointeGestionUuid = $uuid;
        $this->nouvellePieceJointeElement = null;
        $this->dispatch('open-modal', id: 'gerer-piece-jointe');
    }

    public function updatedNouvellePieceJointeElement(): void
    {
        $this->validate([
            'nouvellePieceJointeElement' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'nouvellePieceJointeElement.mimes' => 'La pièce jointe doit être un fichier PDF.',
            'nouvellePieceJointeElement.max' => 'La pièce jointe ne doit pas dépasser 5 Mo.',
        ]);
    }

    public function ajouterPieceJointeSurElement(): void
    {
        if (!$this->pieceJointeGestionType || !$this->pieceJointeGestionUuid || !$this->nouvellePieceJointeElement) {
            return;
        }

        if (!$this->canGererPieceJointe()) {
            Notification::make()->title('Accès refusé')->warning()->send();

            return;
        }

        if (!$this->elementEstValide($this->pieceJointeGestionType, $this->pieceJointeGestionUuid)) {
            Notification::make()
                ->title('Action impossible')
                ->body('La pièce jointe ne peut être ajoutée que sur un élément validé.')
                ->warning()
                ->send()
            ;

            return;
        }

        $directory = sprintf(
            'fcmcentral/livretfcm/pieces-jointes/%s/%s/%s/%s',
            $this->pieceJointeGestionType,
            $this->marin->uuid,
            now()->format('Y'),
            now()->format('m')
        );
        $nouveauChemin = $this->nouvellePieceJointeElement->store($directory, 's3-private');

        // ✅ Passe par l'event/listener (même schéma fiable que valider()/devalider()), qui fait
        // la mutation JSON du parcours en base et nettoie l'éventuel ancien fichier remplacé.
        $event = new ($this->UserGeneratedEvent)(
            event_type: UserGeneratedEventTypes::AJOUT_PIECE_JOINTE_MARIN_PARCOURS,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->uuid,
            detail: [
                'type_element' => $this->pieceJointeGestionType,
                'uuid_element' => $this->pieceJointeGestionUuid,
                'piece_jointe' => $nouveauChemin,
            ]
        );
        event($event);

        $this->pieceJointeGestionType = null;
        $this->pieceJointeGestionUuid = null;
        $this->nouvellePieceJointeElement = null;

        $this->marin->refresh();
        unset($this->parcours);
        unset($this->parcoursFiltre);
        $this->dispatch('close-modal', id: 'gerer-piece-jointe');
        $this->dispatch('$refresh');
    }

    public function supprimerPieceJointeSurElement(string $type, string $uuid): void
    {
        if (!$this->canGererPieceJointe()) {
            Notification::make()->title('Accès refusé')->warning()->send();

            return;
        }

        if (!$this->elementEstValide($type, $uuid)) {
            Notification::make()
                ->title('Action impossible')
                ->body('La pièce jointe ne peut être supprimée que sur un élément validé.')
                ->warning()
                ->send()
            ;

            return;
        }

        // ✅ Passe par l'event/listener : c'est lui qui retrouve le chemin S3 actuel, le supprime
        // et retire la référence du JSON, en une seule opération cohérente.
        $event = new ($this->UserGeneratedEvent)(
            event_type: UserGeneratedEventTypes::SUPPR_PIECE_JOINTE_MARIN_PARCOURS,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->uuid,
            detail: [
                'type_element' => $type,
                'uuid_element' => $uuid,
            ]
        );
        event($event);

        $this->marin->refresh();
        unset($this->parcours);
        unset($this->parcoursFiltre);
        $this->dispatch('$refresh');
    }

    private function elementEstValide(string $type, string $uuid): bool
    {
        $element = $this->trouverElementBrut($type, $uuid);

        return (bool) ($element['etat_valid'] ?? false);
    }

    /**
     * Cherche un élément (savoirfaire ou objectif) par uuid dans le parcours brut (non filtré).
     * Utilisé uniquement pour les vérifications côté UI (ex. etat_valid) ; la mutation réelle
     * du parcours se fait désormais dans PieceJointeMarinParcoursListener (FcmCentral), via
     * event/listener — même schéma fiable que valider()/devalider() dans HasValidationActions.
     */
    private function trouverElementBrut(string $type, string $uuid): ?array
    {
        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    if ('savoirfaire' === $type && ($csf['uuid'] ?? null) === $uuid) {
                        return $csf;
                    }
                    if ('objectif' === $type) {
                        foreach ($csf['objectifs'] ?? [] as $objectif) {
                            if (($objectif['uuid'] ?? null) === $uuid) {
                                return $objectif;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }
}
