<?php

namespace Modules\FcmCommun\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Events\UserGeneratedEvent;

class LivretFcmLightNew extends Component
{
    #[Locked]
    public $marinId;

    // Variables internes pour éviter les recalculs dans le même cycle
    private ?array $savoirFaireChildrenCache = null;
    private ?array $parcoursCache = null;
    private ?array $statistiquesCache = null;

    public function mount(Marin $marin)
    {
        $this->marinId = $marin->id;
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
        if ($this->parcoursCache === null) {
            $this->parcoursCache = $this->getParcoursFromDatabase();
        }
        return $this->parcoursCache;
    }

    #[Computed]
    public function savoirFaireChildren()
    {
        if ($this->savoirFaireChildrenCache === null) {
            $this->savoirFaireChildrenCache = $this->generateSavoirFaireMapping();
        }
        return $this->savoirFaireChildrenCache;
    }

    #[Computed]
    public function isCurrentUser()
    {
        return $this->marin->isCurrentUser();
    }

    #[Computed]
    public function statistiques()
    {
        if ($this->statistiquesCache === null) {
            $this->statistiquesCache = $this->calculateStatistiques();
        }
        return $this->statistiquesCache;
    }

    // ========================================
    // MÉTHODES DE RENDU
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
    // RÉCUPÉRATION DU PARCOURS
    // ========================================

    private function getParcoursFromDatabase(): ?array
    {
        try {
            // ✅ Requête directe vers fcmuser_parcours
            $parcoursData = DB::connection('fcmcentral')
                ->table('fcmefcm_marin_parcours')
                ->where('marin_id', $this->marinId)
                ->select(['parcours_data'])
                ->first();

            if (!$parcoursData) {
                Log::info("Aucun parcours trouvé pour marin {$this->marinId}");
                return null;
            }

            // Décoder le JSON
            $parcours = json_decode($parcoursData->parcours_data, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Erreur JSON pour parcours marin {$this->marinId}: " . json_last_error_msg());
                return null;
            }

            return $parcours;

        } catch (\Exception $e) {
            Log::error("Erreur récupération parcours marin {$this->marinId}: " . $e->getMessage());
            return null;
        }
    }

    // ========================================
    // GÉNÉRATION DU MAPPING SAVOIR-FAIRE
    // ========================================

    private function generateSavoirFaireMapping(): array
    {
        $parcours = $this->parcours;
        
        if (!$parcours || !isset($parcours['fonctions'])) {
            return [];
        }

        $mapping = [];
        
        foreach ($parcours['fonctions'] as $fonction) {
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

    // ========================================
    // CALCUL DES STATISTIQUES
    // ========================================

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

        $stats = [
            'total_activites' => 0,
            'activites_validees' => 0,
            'activites_proposees' => 0,
            'activites_en_attente' => 0,
            'derniere_activite' => null
        ];

        $derniereDate = null;

        foreach ($parcours['fonctions'] ?? [] as $fonction) {
            foreach ($fonction['competences'] ?? [] as $competence) {
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    foreach ($savoirfaire['activites'] ?? [] as $activite) {
                        $stats['total_activites']++;
                        
                        // États des activités
                        if (($activite['etat_valid'] ?? false) === true) {
                            $stats['activites_validees']++;
                            
                            // Tracker la dernière validation
                            if (!empty($activite['date_validation'])) {
                                $dateValidation = Carbon::parse($activite['date_validation']);
                                if (!$derniereDate || $dateValidation->isAfter($derniereDate)) {
                                    $derniereDate = $dateValidation;
                                    $stats['derniere_activite'] = $activite['libelle_court'] ?? 'Activité';
                                }
                            }
                        } elseif (!empty($activite['date_proposition'])) {
                            $stats['activites_proposees']++;
                            if (empty($activite['date_validation'])) {
                                $stats['activites_en_attente']++;
                            }
                        }
                    }
                }
            }
        }

        $stats['taux_completion'] = $stats['total_activites'] > 0 
            ? round(($stats['activites_validees'] / $stats['total_activites']) * 100, 1)
            : 0;

        return $stats;
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
            
            // Traitement par lot
            DB::transaction(function() use ($events, $commentaire) {
                foreach ($events as $eventData) {
                    $this->createEvent(
                        UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
                        $eventData['type'],
                        $eventData['uuid'],
                        $commentaire
                    );
                }
            });

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

            $this->clearInternalCache();
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

    // ========================================
    // GESTION DU REFRESH
    // ========================================

    private function clearInternalCache(): void
    {
        $this->savoirFaireChildrenCache = null;
        $this->parcoursCache = null;
        $this->statistiquesCache = null;
        
        // Réinitialiser les propriétés computed
        unset($this->marin);
        unset($this->parcours);
        unset($this->statistiques);
        unset($this->savoirFaireChildren);
    }

    private function refreshAndReload(): void
    {
        $this->clearInternalCache();
        $this->js('window.location.reload()');
    }

    // ========================================
    // MÉTHODES UTILITAIRES PUBLIQUES
    // ========================================

    public function refreshData(): void
    {
        $this->clearInternalCache();
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
    // MÉTHODES DE DEBUG
    // ========================================

    public function getDebugInfo(): array
    {
        return [
            'marin_id' => $this->marinId,
            'has_parcours' => !is_null($this->parcours),
            'memory_usage' => memory_get_usage(true),
            'statistiques' => $this->statistiques,
        ];
    }
}
