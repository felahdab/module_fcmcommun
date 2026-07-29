<?php

namespace Modules\FcmCommun\Http\Livewire\Concerns\LivretFcmLight;

use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Modules\FcmCommun\Services\CalculHeuresParcoursService;

trait HasFiltres
{
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
        $total   = 0;
        $valides = 0;
        $proposes = 0;

        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    $total++;
                    if ($csf['etat_valid'] ?? false)         $valides++;
                    if (!empty($csf['date_proposition']))    $proposes++;

                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        $total++;
                        if ($objectif['etat_valid'] ?? false)      $valides++;
                        if (!empty($objectif['date_proposition'])) $proposes++;
                    }
                }
            }
        }

        // ✅ Taux basé sur les heures (compagnonnage inclus) via le service
        $taux = CalculHeuresParcoursService::calculerPourcentageEtatValid($this->parcours);

        return [
            'total'      => $total,
            'valides'    => $valides,
            'proposes'   => $proposes,
            'non_valides' => $total - $valides,
            'taux'       => $taux,
        ];
    }

    protected function getStatistiquesGenerales(): array
    {
        $parcours = $this->marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? [];

        if (empty($parcours)) {
            return [
                'affectations_total'    => 0,
                'affectations_validees' => 0,
                'heures_total'          => 0,
                'heures_validees'       => 0,
                'taux_heures'           => 0,
            ];
        }

        $affectationsTotal    = count($parcours['affectations'] ?? []);
        $affectationsValidees = collect($parcours['affectations'] ?? [])
            ->where('etat_valid', true)
            ->count();

        // ✅ Lecture des heures déjà calculées par CalculHeuresParcoursService
        // (renseignées sur chaque affectation par mettreAJourHeures dans les listeners)
        $heuresTotal    = 0;
        $heuresValidees = 0;
        foreach ($parcours['affectations'] ?? [] as $affectation) {
            $heuresTotal    += $affectation['heures_total']    ?? 0;
            $heuresValidees += $affectation['heures_validees'] ?? 0;
        }

        // ✅ Taux via le service (gère compagnonnage, jours→heures, objectifs)
        $taux = CalculHeuresParcoursService::calculerPourcentageEtatValid($parcours);

        return [
            'affectations_total'    => $affectationsTotal,
            'affectations_validees' => $affectationsValidees,
            'heures_total'          => round($heuresTotal, 0),
            'heures_validees'       => round($heuresValidees, 0),
            'taux_heures'           => $taux,
        ];
    }
}
