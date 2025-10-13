<?php

namespace Modules\FcmCommun\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCommun\Services\OutilsMarinParcoursService;
use Modules\FcmCommun\Services\OutilsFcmLightService;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Traits\FcmAccessControl;

class LivretFcmLight extends Component
{
    use FcmAccessControl;

    #[Locked]
    public $marinId;

    private OutilsMarinParcoursService $service;

    // ========================================
    // PROPRIÉTÉS DE FILTRAGE
    // ========================================
   
    public string $search = '';
    public string $statutFilter = 'tous';
    public string $anneeFilter = 'toutes';
    public string $competenceFilter = 'toutes';
    public string $savoirfaireTypeFilter = 'tous';

    protected $queryString = [
        'search' => ['except' => ''],
        'statutFilter' => ['except' => 'tous'],
        'anneeFilter' => ['except' => 'toutes'],
        'competenceFilter' => ['except' => 'toutes'],
        'savoirfaireTypeFilter' => ['except' => 'tous'],
    ];

    // ========================================
    // LIFECYCLE METHODS
    // ========================================

    public function mount(Marin $marin)
    {
        $this->marinId = $marin->id;
    }

    public function boot()
    {
        $this->service = new OutilsMarinParcoursService();
    }

    // ========================================
    // COMPUTED PROPERTIES - DONNÉES DE BASE
    // ========================================

    #[Computed]
    public function marin()
    {
        return Marin::select(['id', 'uuid', 'nom', 'prenom'])
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

    // ========================================
    // COMPUTED PROPERTIES - DONNÉES POUR FILTRES
    // ========================================

    #[Computed]
    public function anneesDisponibles()
    {
        if (!$this->parcours) return [];

        $annees = [];
        foreach ($this->parcours['annees'] ?? [] as $annee) {
            $anneeId = $annee['id'] ?? $annee['uuid'] ?? null;
           
            if ($anneeId && !isset($annees[$anneeId])) {
                $anneeInfos = OutilsFcmLightService::getAnneeInfos((int)$anneeId);
                $annees[$anneeId] = $anneeInfos;
            }
        }

        uasort($annees, fn($a, $b) => $a['ordre'] <=> $b['ordre']);
        return $annees;
    }

    #[Computed]
    public function competencesDisponibles()
    {
        if (!$this->parcours) return [];

        $competences = [];
        foreach ($this->parcours['annees'] ?? [] as $annee) {
            foreach ($annee['savoirfaires'] ?? [] as $savoirfaire) {
                $competenceId = $this->getCompetenceId($savoirfaire);
               
                if ($competenceId && !isset($competences[$competenceId])) {
                    $competenceInfos = OutilsFcmLightService::getCompetenceInfos($competenceId);
                    $competences[$competenceId] = $competenceInfos;
                }
            }
        }

        uasort($competences, fn($a, $b) => $a['code'] <=> $b['code']);
        return $competences;
    }

    #[Computed]
    public function savoirfaireTypesDisponibles()
    {
        if (!$this->parcours) return [];

        $typesUtilises = [];
       
        foreach ($this->parcours['annees'] ?? [] as $annee) {
            foreach ($annee['savoirfaires'] ?? [] as $savoirfaire) {
                $typeId = $this->getSavoirfaireTypeId($savoirfaire);
               
                if ($typeId && !isset($typesUtilises[$typeId])) {
                    $typeInfos = OutilsFcmLightService::getSavoirfaireTypeInfos($typeId);
                    $typesUtilises[$typeId] = $typeInfos;
                }
            }
        }

        uasort($typesUtilises, fn($a, $b) => $a['ordre'] <=> $b['ordre']);
        return $typesUtilises;
    }

    // ✅ COMPUTED PROPERTIES SIMPLIFIÉES
    
    #[Computed]
    public function tousLesSavoirfaireTypes()
    {
        return OutilsFcmLightService::getSavoirfaireTypeLibellesCourt();
    }

    #[Computed]
    public function toutesLesCompetences()
    {
        return OutilsFcmLightService::getCompetences()->pluck('libelle_court', 'id')->toArray();
    }

    #[Computed]
    public function toutesLesAnnees()
    {
        return OutilsFcmLightService::getAnneeLibellesCourt();
    }

    #[Computed]
    public function canPropose()
    {
        return $this->canAccessMarinLivret();
    }

    #[Computed]
    public function canValidateElements()
    {
        return $this->canValidateElements();
    }

    // ✅ SIMPLIFIÉ - Utilise directement les objectifs du parcours sérialisé
    #[Computed]
    public function savoirFaireChildren()
    {
        if (!$this->parcours) {
            return [];
        }

        $mapping = [];
       
        foreach ($this->parcours['annees'] ?? [] as $annee) {
            foreach ($annee['savoirfaires'] ?? [] as $savoirfaire) {
                $pivotId = $savoirfaire['pivot']['id'] ?? null;
               
                if ($pivotId) {
                    $uniqueUuid = 'pivot_' . $pivotId;
                    $objectifs = $savoirfaire['objectifs'] ?? []; // ✅ Directement depuis les données
                   
                    // ✅ Créer les UUID des objectifs
                    $objectifUuids = [];
                    foreach ($objectifs as $objectif) {
                        $objectifUuids[] = 'obj_' . $pivotId . '_' . $objectif['id'];
                    }
                   
                    $mapping[$uniqueUuid] = $objectifUuids;
                }
            }
        }

        return $mapping;
    }

    #[Computed]
    public function isCurrentUser()
    {
        return $this->marin->isCurrentUser();
    }

    #[Computed]
    public function statistiques()
    {
        if (!$this->parcours) {
            return [
                'total' => 0,
                'valides' => 0,
                'proposes' => 0,
                'non_valides' => 0
            ];
        }

        return $this->calculerStatistiques();
    }

    #[Computed]
    public function hasActiveFilters()
    {
        return !empty($this->search) ||
               $this->statutFilter !== 'tous' ||
               $this->anneeFilter !== 'toutes' ||
               $this->competenceFilter !== 'toutes' ||
               $this->savoirfaireTypeFilter !== 'tous';
    }

    // ========================================
    // MÉTHODES HELPER POUR EXTRACTION DES IDs
    // ========================================

    private function getCompetenceId(array $savoirfaire): ?int
    {
        $competenceId = $savoirfaire['competence_id'] ??
                       $savoirfaire['pivot']['competence_id'] ??
                       null;
       
        return $competenceId ? (int)$competenceId : null;
    }

    private function getSavoirfaireTypeId(array $savoirfaire): ?int
    {
        $typeId = $savoirfaire['savoirfaire_type_id'] ??
                 $savoirfaire['pivot']['savoirfaire_type_id'] ??
                 null;
       
        return $typeId ? (int)$typeId : null;
    }

    // ========================================
    // MÉTHODES HELPER POUR AFFICHAGE
    // ========================================

    public function getCompetenceCode(array $savoirfaire): string
    {
        $competenceId = $this->getCompetenceId($savoirfaire);
        if (!$competenceId) return 'COMP';
       
        $competenceInfos = OutilsFcmLightService::getCompetenceInfos($competenceId);
        return $competenceInfos['code'];
    }

    public function getCompetenceColor(array $savoirfaire): string
    {
        $competenceId = $this->getCompetenceId($savoirfaire);
        if (!$competenceId) return '#6b7280';
       
        $competenceInfos = OutilsFcmLightService::getCompetenceInfos($competenceId);
        return $competenceInfos['color'];
    }

    public function getSavoirfaireTypeCode(array $savoirfaire): string
    {
        $typeId = $this->getSavoirfaireTypeId($savoirfaire);
        if (!$typeId) return 'SF';
       
        $typeInfos = OutilsFcmLightService::getSavoirfaireTypeInfos($typeId);
        return $typeInfos['code'];
    }

    public function getSavoirfaireTypeColor(array $savoirfaire): string
    {
        $typeId = $this->getSavoirfaireTypeId($savoirfaire);
        if (!$typeId) return '#3b82f6';
       
        $typeInfos = OutilsFcmLightService::getSavoirfaireTypeInfos($typeId);
        return $typeInfos['color'];
    }

    // ========================================
    // MÉTHODES DE FILTRAGE
    // ========================================

    public function filtrerParStatut(string $statut)
    {
        $this->statutFilter = $statut;
        Log::info("=== FILTRE PAR STATUT: {$statut} ===");
    }

    public function filtrerParAnnee(string $anneeId)
    {
        $this->anneeFilter = $anneeId;
       
        if ($anneeId !== 'toutes') {
            $anneeInfos = OutilsFcmLightService::getAnneeInfos((int)$anneeId);
            Log::info("=== FILTRE PAR ANNÉE: {$anneeId} ({$anneeInfos['libelle_court']}) ===");
        } else {
            Log::info("=== FILTRE PAR ANNÉE: TOUTES ===");
        }
    }

    public function filtrerParCompetence(string $competenceId)
    {
        $this->competenceFilter = $competenceId;
       
        if ($competenceId !== 'toutes') {
            $competenceInfos = OutilsFcmLightService::getCompetenceInfos((int)$competenceId);
            Log::info("=== FILTRE PAR COMPÉTENCE: {$competenceId} ({$competenceInfos['code']}) ===");
        } else {
            Log::info("=== FILTRE PAR COMPÉTENCE: TOUTES ===");
        }
    }

    public function filtrerParSavoirfaireType(string $typeId)
    {
        $this->savoirfaireTypeFilter = $typeId;
       
        if ($typeId !== 'tous') {
            $typeInfos = OutilsFcmLightService::getSavoirfaireTypeInfos((int)$typeId);
            Log::info("=== FILTRE PAR TYPE SF: {$typeId} ({$typeInfos['code']}) ===");
        } else {
            Log::info("=== FILTRE PAR TYPE SF: TOUS ===");
        }
    }

    public function resetAllFilters()
    {
        $this->search = '';
        $this->statutFilter = 'tous';
        $this->anneeFilter = 'toutes';
        $this->competenceFilter = 'toutes';
        $this->savoirfaireTypeFilter = 'tous';
    }

    // ========================================
    // LOGIQUE DE FILTRAGE - MÉTHODES PRIVÉES
    // ========================================

    private function appliquerFiltres(array $parcours): array
    {
        $parcoursFiltre = $parcours;

        if (isset($parcoursFiltre['annees'])) {
            $anneesFiltrees = [];
           
            foreach ($parcoursFiltre['annees'] as $annee) {
                if (!$this->anneeCorrespondAuFiltre($annee)) {
                    continue;
                }

                $anneeFiltre = $this->filtrerAnnee($annee);
                if ($anneeFiltre !== null) {
                    $anneesFiltrees[] = $anneeFiltre;
                }
            }
           
            $parcoursFiltre['annees'] = $anneesFiltrees;
        }

        return $parcoursFiltre;
    }

    private function anneeCorrespondAuFiltre(array $annee): bool
    {
        if ($this->anneeFilter === 'toutes') {
            return true;
        }

        $anneeId = $annee['id'] ?? $annee['uuid'] ?? '';
        return (string)$anneeId === $this->anneeFilter;
    }

    private function filtrerAnnee(array $annee): ?array
    {
        $anneeFiltre = $annee;
        $savoirfairesFiltres = [];

        if (isset($annee['savoirfaires'])) {
            foreach ($annee['savoirfaires'] as $savoirfaire) {
                if (!$this->savoirfaireCorrespondAuxFiltresGeneraux($savoirfaire)) {
                    continue;
                }

                $savoirfaireFiltre = $this->filtrerSavoirFaire($savoirfaire);
                if ($savoirfaireFiltre !== null) {
                    $savoirfairesFiltres[] = $savoirfaireFiltre;
                }
            }
        }

        if (empty($savoirfairesFiltres)) {
            return null;
        }

        $anneeFiltre['savoirfaires'] = $savoirfairesFiltres;
        return $anneeFiltre;
    }

    private function savoirfaireCorrespondAuxFiltresGeneraux(array $savoirfaire): bool
    {
        // Filtre par compétence
        if ($this->competenceFilter !== 'toutes') {
            $competenceId = $this->getCompetenceId($savoirfaire);
           
            if ((string)$competenceId !== $this->competenceFilter) {
                return false;
            }
        }

        // Filtre par type de savoir-faire
        if ($this->savoirfaireTypeFilter !== 'tous') {
            $typeId = $this->getSavoirfaireTypeId($savoirfaire);
           
            if ((string)$typeId !== $this->savoirfaireTypeFilter) {
                return false;
            }
        }

        return true;
    }

    private function filtrerSavoirFaire(array $savoirfaire): ?array
    {
        $savoirfaireCorrespond = $this->elementCorrespondAuxFiltres($savoirfaire, 'savoirfaire');
       
        $objectifsFiltres = [];
        if (isset($savoirfaire['objectifs'])) {
            foreach ($savoirfaire['objectifs'] as $objectif) {
                if ($this->elementCorrespondAuxFiltres($objectif, 'objectif')) {
                    $objectifsFiltres[] = $objectif;
                }
            }
        }

        if (!$savoirfaireCorrespond && empty($objectifsFiltres)) {
            return null;
        }

        $savoirfaireFiltre = $savoirfaire;
        $savoirfaireFiltre['objectifs'] = $objectifsFiltres;

        return $savoirfaireFiltre;
    }

    private function elementCorrespondAuxFiltres(array $element, string $type): bool
    {
        // Recherche textuelle
        if (!empty($this->search)) {
            if (!in_array($type, ['savoirfaire', 'objectif'])) {
                return true;
            }
           
            if (!$this->correspondRecherche($element)) {
                return false;
            }
        }

        // Filtre par statut
        if (in_array($type, ['savoirfaire', 'objectif']) && $this->statutFilter !== 'tous') {
            return $this->verifierStatut($element);
        }

        return true;
    }

    private function correspondRecherche(array $element): bool
    {
        $recherche = strtolower(trim($this->search));
        $texteElement = strtolower(
            ($element['libelle_court'] ?? '') . ' '
            .($element['libelle_long'] ?? '') . ' '
            .($element['code'] ?? '') . ' '
        );
       
        return str_contains($texteElement, $recherche);
    }

    private function verifierStatut(array $element): bool
    {
        $etatValid = ($element['etat_valid'] ?? 0);
        $dateProposition = $element['date_proposition'] ?? null;
       
        $estValide = ($etatValid === true);
        $estPropose = !empty($dateProposition);
        $nonValide = ($etatValid == null);

        return match($this->statutFilter) {
            'valides' => $estValide,
            'proposes' => $estPropose,
            'non_valides' => $nonValide && !$estPropose,
            default => true
        };
    }

    // ========================================
    // CALCULS ET STATISTIQUES
    // ========================================

    private function calculerStatistiques(): array
    {
        $total = 0;
        $valides = 0;
        $proposes = 0;

        foreach ($this->parcours['annees'] ?? [] as $annee) {
            foreach ($annee['savoirfaires'] ?? [] as $savoirfaire) {
                $total++;
               
                $etatValid = ($savoirfaire['etat_valid'] ?? 0) == 1;
                $dateProposition = !empty($savoirfaire['date_proposition']);
               
                if ($etatValid) {
                    $valides++;
                }
                if ($dateProposition) {
                    $proposes++;
                }

                foreach ($savoirfaire['objectifs'] ?? [] as $objectif) {
                    $total++;
                   
                    $etatValidObjectif = ($objectif['etat_valid'] ?? 0) == 1;
                    $datePropositionObjectif = !empty($objectif['date_proposition']);
                   
                    if ($etatValidObjectif) {
                        $valides++;
                    }
                    if ($datePropositionObjectif) {
                        $proposes++;
                    }
                }
            }
        }

        return [
            'total' => $total,
            'valides' => $valides,
            'proposes' => $proposes,
            'non_valides' => $total - $valides
        ];
    }

    // ========================================
    // MÉTHODES UTILITAIRES
    // ========================================

    public function getSavoirfaireTypeInfos(int $typeId): array
    {
        return OutilsFcmLightService::getSavoirfaireTypeInfos($typeId);
    }

    public function escapeForTooltip($text): string
    {
        if (empty($text)) {
            return '';
        }
       
        return htmlspecialchars(
            str_replace(["\r\n", "\n", "\r", "'", '"'], [' ', ' ', ' ', "&#39;", "&#34;"], $text),
            ENT_QUOTES,
            'UTF-8'
        );
    }

    public function escapeForJs($text): string
    {
        if (empty($text)) {
            return '';
        }
       
        return addslashes(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
    }

    // ========================================
    // ACTIONS DE VALIDATION/PROPOSITION
    // ========================================

    public function devalider(array $element_a_devalider)
    {
        logger()->info("Dévalider: ", $element_a_devalider);

        $event = new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::ANNUL_ELMT_MARIN_PARCOURS,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->uuid,
            detail: [
                "type_element" => $element_a_devalider['type'],
                "uuid_element" => $element_a_devalider['uuid'],
                "date" => Carbon::now(),
            ]
        );
        event($event);

        $this->refreshAfterAction();
    }

    public function valider(array $elements_a_valider, string $commentaire)
    {
        logger()->info("Valider: ", $elements_a_valider);

        if (array_key_exists("savoirfaire", $elements_a_valider)){
            foreach($elements_a_valider["savoirfaire"] as $savoirfaire_uuid => $bool) {
                if (!$bool) continue;
               
                $this->createValidationEvent("savoirfaire", $savoirfaire_uuid, $commentaire);
            }
        }

        if (array_key_exists("objectif", $elements_a_valider)){
            foreach($elements_a_valider["objectif"] as $objectif_uuid => $bool) {
                if (!$bool) continue;
               
                $this->createValidationEvent("objectif", $objectif_uuid, $commentaire);
            }
        }

        $this->refreshAfterAction();
    }

    public function proposer(array $element_a_proposer, string $commentaire)
    {
        logger()->info("Proposer: ", $element_a_proposer);

        $event = new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::PROPOS_ELMT_MARIN_PARCOURS,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->uuid,
            detail: [
                "type_element" => $element_a_proposer['type'],
                "uuid_element" => $element_a_proposer['uuid'],
                "date" => Carbon::now(),
                "commentaire" => $commentaire
            ]
        );
        event($event);

        $this->refreshAfterAction();
    }

    // ========================================
    // MÉTHODES HELPER POUR ACTIONS
    // ========================================

    private function createValidationEvent(string $type, string $uuid, string $commentaire): void
    {
        $event = new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->uuid,
            detail: [
                "type_element" => $type,
                "uuid_element" => $uuid,
                "date" => Carbon::now(),
                "commentaire" => $commentaire,
            ]
        );
        event($event);
    }

    private function refreshAfterAction(): void
    {
        $this->marin->refresh();
       
        unset($this->parcours);
        unset($this->parcoursFiltre);
        unset($this->statistiques);
       
        $this->dispatch('clear-selections');
        $this->dispatch('$refresh');
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
            'canPropose' => $this->canPropose,
            'statistiques' => $this->statistiques,
           
            'anneesDisponibles' => $this->anneesDisponibles,
            'competencesDisponibles' => $this->competencesDisponibles,
            'savoirfaireTypesDisponibles' => $this->savoirfaireTypesDisponibles,
            'tousLesSavoirfaireTypes' => $this->tousLesSavoirfaireTypes,
            'hasActiveFilters' => $this->hasActiveFilters,
        ]);
    }
}
