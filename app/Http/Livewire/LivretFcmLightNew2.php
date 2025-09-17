<?php

namespace Modules\FcmCommun\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCommun\Services\OutilsMarinParcoursService;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Events\UserGeneratedEvent;

class LivretFcmLightNew2 extends Component
{
    #[Locked]
    public $marinId;

    private OutilsMarinParcoursService $outilsService;

    public function mount(Marin $marin)
    {
        $this->marinId = $marin->id;
        $this->outilsService = new OutilsMarinParcoursService();
    }

    // ========================================
    // PROPRIÉTÉS COMPUTED
    // ========================================

    #[Computed]
    public function marin()
    {
        return Marin::select(['id', 'uuid', 'nom', 'prenom', 'grade_id'])
            ->with(['grade:id,libelle_court'])
            ->find($this->marinId);
    }

    #[Computed]
    public function parcours()
    {
        return $this->getParcoursFromDatabase();
    }

    #[Computed]
    public function savoirFaireChildren()
    {
        return $this->generateSavoirFaireMapping();
    }

    #[Computed]
    public function statistiques()
    {
        return $this->calculateStatistiques();
    }

    #[Computed]
    public function isCurrentUser()
    {
        return $this->marin->isCurrentUser();
    }

    // ========================================
    // RÉCUPÉRATION DU PARCOURS
    // ========================================

    private function getParcoursFromDatabase(): ?array
    {
        try {
            $marin = Marin::select(['id'])
                ->with(['complements_fcm' => function($query) {
                    $query->select(['id', 'rh_marin_id', 'donnees_du_parcours']);
                }])
                ->find($this->marinId);

            if (!$marin || !$marin->complements_fcm || !$marin->complements_fcm->donnees_du_parcours) {
                Log::info("Aucun parcours trouvé pour marin {$this->marinId}");
                return null;
            }

            $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
            
            if (!$parcours || !is_array($parcours)) {
                Log::warning("Parcours invalide pour marin {$this->marinId}");
                return null;
            }

            return $parcours;

        } catch (\Exception $e) {
            Log::error("Erreur récupération parcours marin {$this->marinId}: " . $e->getMessage());
            return null;
        }
    }

    // ========================================
    // UTILISATION D'OUTILSMARINPARCOURSSERVICE
    // ========================================

    private function generateSavoirFaireMapping(): array
    {
        $parcours = $this->parcours;
        
        if (!$parcours) {
            return [];
        }

        $mapping = [];
        
        foreach ($parcours['fonctions'] ?? [] as $fonction) {
            foreach ($fonction['competences'] ?? [] as $competence) {
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    $mapping[$savoirfaire['uuid']] = array_column(
                        $savoirfaire['activites'] ?? [], 
                        'uuid'
                    );
                }
            }
        }

        return $mapping;
    }

    private function calculateStatistiques(): array
    {
        $parcours = $this->parcours;
        
        if (!$parcours) {
            return [
                'total_activites' => 0,
                'activites_validees' => 0,
                'activites_proposees' => 0,
                'activites_en_attente' => 0,
                'taux_completion' => 0,
                'derniere_activite' => null
            ];
        }

        // ✅ Utiliser OutilsMarinParcoursService pour les calculs
        $activitesValidees = $this->outilsService->activites_valid_du_parcours($parcours);
        $activitesProposees = $this->outilsService->activites_du_parcours_respectant_une_condition(
            fn($activite) => !empty($activite['date_proposition']),
            $parcours
        );
        $activitesEnAttente = $this->outilsService->activites_du_parcours_respectant_une_condition(
            fn($activite) => !empty($activite['date_proposition']) && empty($activite['date_validation']),
            $parcours
        );
        $toutesActivites = $this->outilsService->activites_du_parcours_respectant_une_condition(
            fn($activite) => true,
            $parcours
        );

        $stats = [
            'total_activites' => count($toutesActivites),
            'activites_validees' => count($activitesValidees),
            'activites_proposees' => count($activitesProposees),
            'activites_en_attente' => count($activitesEnAttente),
            'derniere_activite' => $this->getDerniereActiviteValidee($activitesValidees)
        ];

        $stats['taux_completion'] = $stats['total_activites'] > 0 
            ? round(($stats['activites_validees'] / $stats['total_activites']) * 100, 1)
            : 0;

        return $stats;
    }

    private function getDerniereActiviteValidee(array $activitesValidees): ?string
    {
        $derniereDate = null;
        $derniereActivite = null;

        foreach ($activitesValidees as $activite) {
            if (!empty($activite['date_validation'])) {
                $dateValidation = Carbon::parse($activite['date_validation']);
                if (!$derniereDate || $dateValidation->isAfter($derniereDate)) {
                    $derniereDate = $dateValidation;
                    $derniereActivite = $activite['libelle_court'] ?? 'Activité';
                }
            }
        }

        return $derniereActivite;
    }

    // ========================================
    // ACTIONS UTILISATEUR
    // ========================================

    public function devalider(array $element): void
    {
        try {
            $this->createEvent(
                UserGeneratedEventTypes::ANNUL_ELMT_MARIN_PARCOURS,
                $element['type'],
                $element['uuid']
            );

            $this->refreshAndReload();
            
        } catch (\Exception $e) {
            Log::error("Erreur dévalidation: " . $e->getMessage());
            $this->dispatch('error', 'Erreur lors de la dévalidation');
        }
    }

    public function valider(array $elements, string $commentaire): void
    {
        try {
            $events = $this->prepareValidationEvents($elements);
            
            foreach ($events as $eventData) {
                $this->createEvent(
                    UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
                    $eventData['type'],
                    $eventData['uuid'],
                    $commentaire
                );
            }

            $this->refreshAndReload();
            
        } catch (\Exception $e) {
            Log::error("Erreur validation: " . $e->getMessage());
            $this->dispatch('error', 'Erreur lors de la validation');
        }
    }

    public function proposer(array $element, string $commentaire): void
    {
        try {
            $this->createEvent(
                UserGeneratedEventTypes::PROPOS_ELMT_MARIN_PARCOURS,
                $element['type'],
                $element['uuid'],
                $commentaire
            );

            $this->dispatch('success', 'Proposition envoyée avec succès');
            
        } catch (\Exception $e) {
            Log::error("Erreur proposition: " . $e->getMessage());
            $this->dispatch('error', 'Erreur lors de la proposition');
        }
    }

    // ========================================
    // MÉTHODES UTILITAIRES
    // ========================================

    private function prepareValidationEvents(array $elements): array
    {
        $events = [];
        $activitesTraitees = [];

        // Traiter d'abord les savoir-faires
        foreach ($elements['savoirfaire'] ?? [] as $uuid => $selected) {
            if ($selected) {
                $events[] = ['type' => 'savoirfaire', 'uuid' => $uuid];
                
                // Marquer les activités enfants comme traitées
                $activitesEnfants = $this->savoirFaireChildren[$uuid] ?? [];
                $activitesTraitees = array_merge($activitesTraitees, $activitesEnfants);
            }
        }

        // Traiter les activités individuelles
        foreach ($elements['activite'] ?? [] as $uuid => $selected) {
            if ($selected && !in_array($uuid, $activitesTraitees)) {
                $events[] = ['type' => 'activite', 'uuid' => $uuid];
            }
        }

        return $events;
    }

    private function createEvent(
        string $eventType, 
        string $elementType, 
        string $elementUuid, 
        string $commentaire = ''
    ): void {
        $event = new UserGeneratedEvent(
            event_type: $eventType,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->uuid,
            detail: [
                "type_element" => $elementType,
                "uuid_element" => $elementUuid,
                "date" => Carbon::now(),
                "commentaire" => $commentaire,
            ]
        );

        event($event);
    }

    private function refreshAndReload(): void
    {
        // Réinitialiser les propriétés computed pour forcer le refresh
        unset($this->marin);
        unset($this->parcours);
        unset($this->statistiques);
        unset($this->savoirFaireChildren);
        
        $this->js('window.location.reload()');
    }

    // ========================================
    // RENDU
    // ========================================

    public function render()
    {
        return view('fcmcommun::livewire.livretfcmlight.livretfcmlight', [
            'marin' => $this->marin,
            'parcours' => $this->parcours,
            'isCurrentUser' => $this->isCurrentUser,
            'savoirFaireChildren' => $this->savoirFaireChildren,
            'statistiques' => $this->statistiques,
        ]);
    }

    // ========================================
    // MÉTHODES UTILITAIRES PUBLIQUES
    // ========================================

    public function refreshData(): void
    {
        unset($this->marin);
        unset($this->parcours);
        unset($this->statistiques);
        unset($this->savoirFaireChildren);
        
        $this->dispatch('data-refreshed');
    }

    public function getProgressPercentage(): float
    {
        return $this->statistiques['taux_completion'] ?? 0;
    }

    public function hasActivitesEnAttente(): bool
    {
        return ($this->statistiques['activites_en_attente'] ?? 0) > 0;
    }

    // ========================================
    // MÉTHODES POUR UTILISER OUTILSSERVICE
    // ========================================

    public function getActivitesValidees(): array
    {
        $parcours = $this->parcours;
        return $parcours ? $this->outilsService->activites_valid_du_parcours($parcours) : [];
    }

    public function getSavoirFairesValidees(): array
    {
        $parcours = $this->parcours;
        return $parcours ? $this->outilsService->savoirfaires_valid_du_parcours($parcours) : [];
    }

    public function getActivitesProposees(): array
    {
        $parcours = $this->parcours;
        return $parcours ? $this->outilsService->activites_du_parcours_respectant_une_condition(
            fn($activite) => !empty($activite['date_proposition']),
            $parcours
        ) : [];
    }

    public function getTachesValidees(): array
    {
        $parcours = $this->parcours;
        return $parcours ? $this->outilsService->taches_valid_du_parcours($parcours) : [];
    }

    public function getStagesValidees(): array
    {
        $parcours = $this->parcours;
        return $parcours ? $this->outilsService->stages_valid_du_parcours($parcours) : [];
    }

    // ========================================
    // DEBUG ET MONITORING
    // ========================================

    public function getDebugInfo(): array
    {
        return [
            'marin_id' => $this->marinId,
            'has_parcours' => !is_null($this->parcours),
            'memory_usage' => memory_get_usage(true),
            'statistiques' => $this->statistiques,
            'outils_service' => get_class($this->outilsService),
        ];
    }
}
