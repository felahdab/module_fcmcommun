<?php
// filepath: Modules/FcmCommun/Http/Livewire/LivretFcmLight.php

namespace Modules\FcmCommun\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

#TODO: vérifier les dépendances avec FcmCentral et FcmUnite

class LivretFcmLight extends Component
{
    #[Locked]
    public $marinId;

    #[Locked]
    public $Marin;

    #[Locked]
    public $UserGeneratedEvent;

    #[Locked]
    public $PermissionPrefix;

   // private OutilsMarinParcoursService $service;

    // ========================================
    // PROPRIÉTÉS DE FILTRAGE
    // ========================================
   
    public string $search = '';
    public string $statutFilter = 'tous';
    public string $affectationFilter = 'toutes';
    public string $competenceFilter = 'toutes';
    public string $savoirfaireTypeFilter = 'tous';

    protected $queryString = [
        'search' => ['except' => ''],
        'statutFilter' => ['except' => 'tous'],
        'affectationFilter' => ['except' => 'toutes'],
        'competenceFilter' => ['except' => 'toutes'],
        'savoirfaireTypeFilter' => ['except' => 'tous'],
    ];

    

    // ========================================
    // INIT
    // ========================================

    public function mount()
    {

    }

    public function boot()
    {
       // $this->service = new OutilsMarinParcoursService();
    }


    // ========================================
    // PERMISSIONS
    // ========================================

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
    
        
        return auth()->user()->can($this->PermissionPrefix . '::livret.proposer') || $this->userEstLeMarin();
    }

    #[Computed]
    public function canAnnuler()
    {   
        if (!$this->marinPeutProposerOuValider()) {
            return false;
        }
    
        return auth()->user()->can($this->PermissionPrefix . '::livret.annuler');
    }

    #[Computed]
    public function canValider()
    {   
        if (!$this->marinPeutProposerOuValider()) {
            return false;
        }
    
        return auth()->user()->can($this->PermissionPrefix . '::livret.valider');
    }


    // ========================================
    // DONNÉES DE BASE
    // ========================================

    #[Computed]
    public function marin()
    {
        return $this->Marin::select(['id', 'uuid', 'nom', 'prenom'])
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
    // DONNÉES POUR FILTRES
    // ========================================

    #[Computed]
    public function affectationsDisponibles()
    {
        if (!$this->parcours) return [];

        $affectations = [];
        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            $affectationId =  $affectation['uuid'] ?? null;
           
            if ($affectationId && !isset($affectations[$affectationId])) {
                $affectations[$affectationId] = [
                    'id' => $affectation['uuid'],
                    'libelle_court' => $affectation['libelle_court'] ?? 'Affectation',
                    'color' => $affectation['color'] ?? 'gray',
                ];
            }
        }

        return $affectations;
    }

    #[Computed]
    public function competencesDisponibles()
    {
        if (!$this->parcours) return [];

        $competences = [];
        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                $competenceId = $competence['uuid'] ?? null;
               
                if ($competenceId && !isset($competences[$competenceId])) {
                    $competences[$competenceId] = [
                        'id' => $competence['uuid'],
                        'code' => $competence['code'] ?? 'COMP',
                        'libelle_court' => $competence['libelle_court'] ?? '',
                        'color' => $competence['color'] ?? 'gray',
                    ];
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

        $types = [];
       
        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    $typeCode = $csf['savoirfaire_type']['code'] ?? null;
                   
                    if ($typeCode && !isset($types[$typeCode])) {
                        $types[$typeCode] = [
                            'code' => $typeCode,
                            'libelle' => $csf['savoirfaire_type']['libelle_court'] ?? $typeCode,
                            'color' => $csf['savoirfaire_type']['color'] ?? 'info',
                        ];
                    }
                }
            }
        }

        return $types;
    }

    

    #[Computed]
    public function savoirFaireChildren()
    {
        if (!$this->parcours) return [];

        $mapping = [];
       
        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    $pivotUuid = $csf['uuid'] ;
                   
                    $objectifUuids = [];
                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        $objectifUuids[] = $objectif['uuid'] ;
                    }
                   
                    $mapping[$pivotUuid] = $objectifUuids;
                }
            }
        }

        return $mapping;
    }

    // Liste des Savoir faire et  objectifs à griser
    #[Computed]
    public function savoirfairesAGriser()
    {
        if ($this->statutFilter !== 'non_valides' || !$this->parcours) {
            return [];
        }
    
        $savoirfairesAGriser = [];
    
        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    $csfUuid = $csf['uuid'];
                    
                    // ✅ NE PAS GRISER si le savoir-faire est validé
                    $savoirfaireValide = ($csf['etat_valid'] ?? false) === true;
                    if ($savoirfaireValide) {
                        continue;
                    }
                    
                    // ✅ GRISER UNIQUEMENT SI : savoir-faire proposé (et non validé)
                    $savoirfairePropose = !empty($csf['date_proposition']) && !$savoirfaireValide;
                    
                    // ❌ NE PLUS vérifier si un objectif est validé
                    // On ne grise PAS le SF juste parce qu'un objectif enfant est validé
                    
                    if ($savoirfairePropose) {
                        $savoirfairesAGriser[] = $csfUuid;
                    }
                }
            }
        }
    
        return $savoirfairesAGriser;
    }
    

    
    #[Computed]
    public function objectifsAGriser()
    {
        if ($this->statutFilter !== 'non_valides' || !$this->parcours) {
            return [];
        }

        $objectifsAGriser = [];

        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        $objectifUuid = $objectif['uuid'];
                        
                        // ✅ NE PAS GRISER si l'objectif est validé
                        $objectifValide = ($objectif['etat_valid'] ?? false) === true;
                        if ($objectifValide) {
                            continue;
                        }
                        
                        // ✅ GRISER UNIQUEMENT si l'objectif est proposé (et non validé)
                        if (!empty($objectif['date_proposition'])) {
                            $objectifsAGriser[] = $objectifUuid;
                        }
                    }
                }
            }
        }

        return $objectifsAGriser;
    }

    



    #[Computed]
    public function objectifsParSavoirfaire()
    {
        if (!$this->parcours) return [];

        $mapping = [];

        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    $savoirfaireId = $csf['savoirfaire']['uuid'] ?? null;
                
                    if (!$savoirfaireId) continue;

                    // Initialiser le tableau si nécessaire
                    if (!isset($mapping[$savoirfaireId])) {
                        $mapping[$savoirfaireId] = [];
                    }

                    // Fusionner les objectifs (sans doublons)
                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        $objectifId = $objectif['uuid'] ?? null;
                        if ($objectifId && !isset($mapping[$savoirfaireId][$objectifId])) {
                            $mapping[$savoirfaireId][$objectifId] = $objectif;
                        }
                    }
                }
            }
        }

        // Convertir en tableaux indexés
        return array_map(fn($objectifs) => array_values($objectifs), $mapping);
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
               $this->affectationFilter !== 'toutes' ||
               $this->competenceFilter !== 'toutes' ||
               $this->savoirfaireTypeFilter !== 'tous';
    }

    
    // pour nettoyer les tooltip
    #[Computed]
    function cleanForTooltip(string $text): string
    {
        if (empty($text)) {
            return '';
        }
        
        // 1. Décoder les entités HTML multiples
        // $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'); // 2x pour double encodage
        
        // 2. Retirer les backslashes
        $text = stripslashes($text);
        
        // 3. Retirer les accents
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        
        // 4. Retirer tous les caractères spéciaux sauf lettres, chiffres, espaces et ponctuation basique
        $text = preg_replace('/[^a-zA-Z0-9\s\.\,\!\?\-\(\)]/', ' ', $text);
        
        // 5. Nettoyer les espaces multiples
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }

    // ========================================
    // MÉTHODES HELPER POUR AFFICHAGE
    // ========================================

    public function getCompetenceCode(array $csf): string
    {
        return $csf['competence']['code'] ?? 'COMP';
    }

    public function getCompetenceColor(array $csf): string
    {
        return $csf['competence']['color'] ?? 'gray';
    }

    // ========================================
    // MÉTHODES DE FILTRAGE
    // ========================================

    public function filtrerParStatut(string $statut)
    {
        $this->statutFilter = $statut;
        Log::info("=== FILTRE PAR STATUT: {$statut} ===");
    }

    public function filtrerParAffectation(string $affectationId)
    {
        $this->affectationFilter = $affectationId;
        Log::info("=== FILTRE PAR AFFECTATION: {$affectationId} ===");
    }

    public function filtrerParCompetence(string $competenceId)
    {
        $this->competenceFilter = $competenceId;
        Log::info("=== FILTRE PAR COMPÉTENCE: {$competenceId} ===");
    }

    public function filtrerParSavoirfaireType(string $typeCode)
    {
        $this->savoirfaireTypeFilter = $typeCode;
        Log::info("=== FILTRE PAR TYPE SF: {$typeCode} ===");
    }

    public function resetAllFilters()
    {
        $this->search = '';
        $this->statutFilter = 'tous';
        $this->affectationFilter = 'toutes';
        $this->competenceFilter = 'toutes';
        $this->savoirfaireTypeFilter = 'tous';
    }

    // ========================================
    // LOGIQUE DE FILTRAGE - MÉTHODES PRIVÉES
    // ========================================

    private function appliquerFiltres(array $parcours): array
    {
        $parcoursFiltre = $parcours;

        if (isset($parcoursFiltre['affectations'])) {
            $affectationsFiltrees = [];
           
            foreach ($parcoursFiltre['affectations'] as $affectation) {
                if (!$this->affectationCorrespondAuFiltre($affectation)) {
                    continue;
                }

                $affectationFiltre = $this->filtrerAffectation($affectation);
                if ($affectationFiltre !== null) {
                    $affectationsFiltrees[] = $affectationFiltre;
                }
            }
           
            $parcoursFiltre['affectations'] = $affectationsFiltrees;
        }

        return $parcoursFiltre;
    }

    private function affectationCorrespondAuFiltre(array $affectation): bool
    {
        if ($this->affectationFilter === 'toutes') {
            return true;
        }

        $affectationId = $affectation['uuid'] ?? $affectation['uuid'] ?? '';
        return (string)$affectationId === $this->affectationFilter;
    }

    private function filtrerAffectation(array $affectation): ?array
    {
        $affectationFiltre = $affectation;
        $competencesFiltrees = [];

        if (isset($affectation['competences'])) {
            foreach ($affectation['competences'] as $competence) {
                if (!$this->competenceCorrespondAuxFiltres($competence)) {
                    continue;
                }

                $competenceFiltre = $this->filtrerCompetence($competence);
                if ($competenceFiltre !== null) {
                    $competencesFiltrees[] = $competenceFiltre;
                }
            }
        }

        if (empty($competencesFiltrees)) {
            return null;
        }

        $affectationFiltre['competences'] = $competencesFiltrees;
        return $affectationFiltre;
    }

    private function competenceCorrespondAuxFiltres(array $competence): bool
    {
        if ($this->competenceFilter !== 'toutes') {
            $competenceId = $competence['uuid'] ?? null;
           
            if ((string)$competenceId !== $this->competenceFilter) {
                return false;
            }
        }

        return true;
    }

    private function filtrerCompetence(array $competence): ?array
    {
        $competenceFiltre = $competence;
        $csfFiltres = [];

        if (isset($competence['competenceSavoirfaires'])) {
            foreach ($competence['competenceSavoirfaires'] as $csf) {
                if (!$this->csfCorrespondAuxFiltresGeneraux($csf)) {
                    continue;
                }

                $csfFiltre = $this->filtrerCompetenceSavoirfaire($csf);
                if ($csfFiltre !== null) {
                    $csfFiltres[] = $csfFiltre;
                }
            }
        }

        if (empty($csfFiltres)) {
            return null;
        }

        $competenceFiltre['competenceSavoirfaires'] = $csfFiltres;
        return $competenceFiltre;
    }

    private function csfCorrespondAuxFiltresGeneraux(array $csf): bool
    {
        // Filtre par type de savoir-faire
        if ($this->savoirfaireTypeFilter !== 'tous') {
            $typeCode = $csf['savoirfaire_type']['code'] ?? null;
           
            if ($typeCode !== $this->savoirfaireTypeFilter) {
                return false;
            }
        }

        return true;
    }

    private function filtrerCompetenceSavoirfaire(array $csf): ?array
    {
        $csfCorrespond = $this->elementCorrespondAuxFiltres($csf, 'savoirfaire');
       
        $objectifsFiltres = [];
        if (isset($csf['objectifs'])) {
            foreach ($csf['objectifs'] as $objectif) {
                if ($this->elementCorrespondAuxFiltres($objectif, 'objectif')) {
                    $objectifsFiltres[] = $objectif;
                }
            }
        }

        if (!$csfCorrespond && empty($objectifsFiltres)) {
            return null;
        }

        $csfFiltre = $csf;
        $csfFiltre['objectifs'] = $objectifsFiltres;

        return $csfFiltre;
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
        
        // Pour un savoir-faire, chercher dans savoirfaire.libelle_*
        $libelleCourt = '';
        $libelleLong = '';
        $code = '';
        
        if (isset($element['savoirfaire'])) {
            $libelleCourt = $element['savoirfaire']['libelle_court'] ?? '';
            $libelleLong = $element['savoirfaire']['libelle_long'] ?? '';
            $code = $element['savoirfaire']['code'] ?? '';
        } else {
            $libelleCourt = $element['libelle_court'] ?? '';
            $libelleLong = $element['libelle_long'] ?? '';
            $code = $element['code'] ?? '';
        }
        
        $texteElement = strtolower($libelleCourt . ' ' . $libelleLong . ' ' . $code);
       
        return str_contains($texteElement, $recherche);
    }

    private function verifierStatut(array $element): bool
{
    $etatValid = ($element['etat_valid'] ?? false);
    $dateProposition = $element['date_proposition'] ?? null;
    
    $estValide = ($etatValid === true);
    $estPropose = !empty($dateProposition);
    $nonValide = !$estValide;

    return match($this->statutFilter) {
        'valides' => $estValide,
        'proposes' => $estPropose && !$estValide, // Seulement proposés non validés
        'non_valides' => $nonValide, // Non validés (inclut proposés en attente)
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

        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    $total++;
                   
                    if ($csf['etat_valid'] ?? false) {
                        $valides++;
                    }
                    if (!empty($csf['date_proposition'])) {
                        $proposes++;
                    }

                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        $total++;
                       
                        if ($objectif['etat_valid'] ?? false) {
                            $valides++;
                        }
                        if (!empty($objectif['date_proposition'])) {
                            $proposes++;
                        }
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

    protected function getStatistiquesGenerales(): array
    {
        $parcours = $this->marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? [];

        if (empty($parcours)) {
            return [
                'affectations_total' => 0,
                'affectations_validees' => 0,
                'heures_total' => 0,
                'heures_validees' => 0,
                'taux_heures' => 0,
            ];
        }

        $affectationsTotal = count($parcours['affectations'] ?? []);
        $affectationsValidees = collect($parcours['affectations'] ?? [])
            ->where('etat_valid', true)
            ->count();

        // ✅ Calcul des heures totales
        $heuresTotal = 0;
        $heuresValidees = 0;

        foreach ($parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    $heures = (float) ($csf['heures'] ?? (($csf['jours'] ?? 0) * 7));
                    $heuresTotal += $heures;

                    if ($csf['etat_valid'] ?? false) {
                        $heuresValidees += $heures;
                    }
                }
            }
        }

        $tauxHeures =  \Modules\FcmCommun\Services\CalculHeuresParcoursService::calculerPourcentageEtatValid($parcours);

        return [
            'affectations_total' => $affectationsTotal,
            'affectations_validees' => $affectationsValidees,
            'heures_total' => round($heuresTotal, 2),
            'heures_validees' => round($heuresValidees, 2),
            'taux_heures' => $tauxHeures,
        ];
    }


    // ========================================
    // MÉTHODES UTILITAIRES
    // ========================================

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
        if ( ! $this->canAnnuler() )
        {
            logger()->warning("Un utilisateur n'ayant pas l'autorisation d annuler un élément du parcours a tenté de le faire.");
            abort(403, 'Vous n avez pas le droit de dévalider des éléments du parcours de FCM.');
        }

        logger()->info("Dévalider: ", $element_a_devalider);

        $event = new ($this->UserGeneratedEvent)(
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
        if ( ! $this->canValider() )
        {
            logger()->warning("Un utilisateur n'ayant pas l'autorisation de valider un élément du parcours a tenté de le faire.");
            abort(403, 'Vous n avez pas le droit de valider des éléments du parcours de FCM.');
        }

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
        if ( ! $this->canProposer() )
        {
            logger()->warning("Un utilisateur n'ayant pas l'autorisation de proposer un élément du parcours a tenté de le faire.");
            abort(403, 'Vous n avez pas le droit de proposer des éléments du parcours de FCM.');
        }

        logger()->info("Proposer: ", $element_a_proposer);

        $event = new ($this->UserGeneratedEvent)(
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

    //  méthode pour annuler une proposition
    public function annulerProposition(array $element_a_annuler, string $commentaire = '')
    {
        if (!$this->canAnnuler()) {
            logger()->warning("Un utilisateur n'ayant pas l'autorisation d'annuler une proposition a tenté de le faire.");
            abort(403, 'Vous n\'avez pas le droit d\'annuler des propositions.');
        }

        logger()->info("Annuler proposition: ", $element_a_annuler);

        $event = new ($this->UserGeneratedEvent)(
            event_type: UserGeneratedEventTypes::ANNUL_PROPOS_ELMT_MARIN_PARCOURS,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->uuid,
            detail: [
                "type_element" => $element_a_annuler['type'],
                "uuid_element" => $element_a_annuler['uuid'],
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
        $event = new ($this->UserGeneratedEvent)(
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
