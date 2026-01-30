<?php
// filepath: Modules/FcmCommun/Services/OutilsMarinParcoursService.php

namespace Modules\FcmCommun\Services;

use Illuminate\Support\Arr;

use Closure;

class OutilsMarinParcoursService
{
    public $ppa = null;
    public $affectation = null;
    public $competence = null;
    public $savoirfaire = null;
    public $objectif = null;


    public function __construct($ppa = null) 
{
    if (is_string($ppa))
    {
       $ppa = json_decode($ppa, true);
    }
    $this->ppa = $ppa;
}


    // ========================================
    // MÉTHODES GÉNÉRIQUES AVEC CLOSURE
    // ========================================

    /**
     * Retourne tous les objectifs du parcours respectant une condition
     */
    public function objectifs_du_parcours_respectant_une_condition(Closure $predicat, array $ppa = [])
    {
        $result = [];
        foreach ($ppa["affectations"] ?? [] as $affectation) {
            foreach ($affectation["competences"] ?? [] as $competence) {
                foreach ($competence["competenceSavoirfaires"] ?? [] as $csf) {
                    foreach ($csf["objectifs"] ?? [] as $objectif) {
                        if ($predicat($objectif)) {
                            $result[] = $objectif;
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Retourne tous les savoir-faires (competenceSavoirfaires) du parcours respectant une condition
     */
    public function savoirfaires_du_parcours_respectant_une_condition(Closure $predicat, array $ppa = [])
    {
        $result = [];
        foreach ($ppa["affectations"] ?? [] as $affectation) {
            foreach ($affectation["competences"] ?? [] as $competence) {
                foreach ($competence["competenceSavoirfaires"] ?? [] as $csf) {
                    if ($predicat($csf)) {
                        $result[] = $csf;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Retourne toutes les compétences du parcours respectant une condition
     */
    public function competences_du_parcours_respectant_une_condition(Closure $predicat, array $ppa = [])
    {
        $result = [];
        foreach ($ppa["affectations"] ?? [] as $affectation) {
            foreach ($affectation["competences"] ?? [] as $competence) {
                if ($predicat($competence)) {
                    $result[] = $competence;
                }
            }
        }
        return $result;
    }

    /**
     * Retourne toutes les affectations du parcours respectant une condition
     */
    public function affectations_du_parcours_respectant_une_condition(Closure $predicat, array $ppa = [])
    {
        $result = [];
        foreach ($ppa["affectations"] ?? [] as $affectation) {
            if ($predicat($affectation)) {
                $result[] = $affectation;
            }
        }
        return $result;
    }

    /**
     * Retourne la liste des UUIDs des objectifs respectant une condition
     */
    public function liste_des_objectifs_du_parcours_respectant_une_condition(Closure $predicat, array $ppa = [])
    {
        $result = [];
        $objectifs = $this->objectifs_du_parcours_respectant_une_condition($predicat, $ppa);
        foreach ($objectifs as $objectif) {
            $result[] = $objectif['uuid'] ?? $objectif['id'];
        }
        return $result;
    }

    // ========================================
    // OBJECTIFS
    // ========================================

    /**
     * Liste des UUIDs des objectifs validés
     */
    public function liste_objectifs_valid_du_parcours(array $ppa = [], string $affectation = null, string $competence = null, string $savoirfaire = null)
    {
        return $this->liste_des_objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return $objectif["etat_valid"] === true;
        }, $ppa);
    }

    /**
     * Objectifs validés du parcours avec détails
     */
    public function objectifs_valid_du_parcours(array $ppa = [], string $affectation = null, string $competence = null, string $savoirfaire = null)
    {
        $result = [];
        foreach ($this->savoirfaires_du_parcours($ppa, $affectation, $competence) as $csf) {
            if ($savoirfaire == null || $csf['uuid'] == $savoirfaire || $csf['id'] == $savoirfaire) {
                foreach ($csf['objectifs'] ?? [] as $objectif) {
                    if ($objectif['etat_valid'] === true) {
                        $uuid = $objectif['uuid'] ?? $objectif['id'];
                        $result[] = [
                            $uuid => [
                                'etat_valid' => $objectif['etat_valid'],
                                'valideur' => $objectif['valideur'] ?? null,
                                'commentaire' => $objectif['commentaire'] ?? null,
                                'date_validation' => $objectif['date_validation'] ?? null,
                                'date_proposition' => $objectif['date_proposition'] ?? null,
                            ]
                        ];
                    }
                }
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    public function objectifs_valides_ou_proposes_du_parcours(array $ppa = [], string $affectation = null, string $competence = null, string $savoirfaire = null)
    {
        $result = [];
        foreach ($this->savoirfaires_du_parcours($ppa, $affectation, $competence) as $csf) {
            if ($savoirfaire == null || $csf['uuid'] == $savoirfaire || $csf['id'] == $savoirfaire) {
                foreach ($csf['objectifs'] ?? [] as $objectif) {
                    if ($objectif['etat_valid'] === true || Arr::get($objectif, 'etat_propos') === true) {
                        $uuid = $objectif['uuid'] ?? $objectif['id'];
                        $result[] = [
                            $uuid => [
                                'etat_valid' => $objectif['etat_valid'],
                                'etat_propos' => Arr::get($objectif, 'etat_propos', false),
                                'valideur' => $objectif['valideur'] ?? null,
                                'commentaire' => $objectif['commentaire'] ?? null,
                                'commentaire_proposition' => $objectif['commentaire_proposition'] ?? null,
                                'date_validation' => $objectif['date_validation'] ?? null,
                                'date_proposition' => $objectif['date_proposition'] ?? null,
                            ]
                        ];
                    }
                }
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    /**
     * Objectifs non validés du parcours
     */
    public function objectifs_nonvalid_du_parcours(array $ppa = [], string $affectation = null, string $competence = null, string $savoirfaire = null)
    {
        $result = [];
        foreach ($this->savoirfaires_du_parcours($ppa, $affectation, $competence) as $csf) {
            if ($savoirfaire == null || $csf['uuid'] == $savoirfaire || $csf['id'] == $savoirfaire) {
                foreach ($csf['objectifs'] ?? [] as $objectif) {
                    if ($objectif['etat_valid'] !== true) {
                        $uuid = $objectif['uuid'] ?? $objectif['id'];
                        $result[] = [
                            $uuid => [
                                'etat_valid' => $objectif['etat_valid'],
                                'valideur' => $objectif['valideur'] ?? null,
                                'commentaire' => $objectif['commentaire'] ?? null,
                                'date_validation' => $objectif['date_validation'] ?? null,
                                'date_proposition' => $objectif['date_proposition'] ?? null,
                            ]
                        ];
                    }
                }
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    /**
     * Objectifs proposés du parcours
     */
    public function objectifs_propos_du_parcours(array $ppa = [], string $affectation = null, string $competence = null, string $savoirfaire = null)
    {
        $result = [];
        foreach ($this->savoirfaires_du_parcours($ppa, $affectation, $competence) as $csf) {
            if ($savoirfaire == null || $csf['uuid'] == $savoirfaire || $csf['id'] == $savoirfaire) {
                foreach ($csf['objectifs'] ?? [] as $objectif) {
                    if (!empty($objectif['date_proposition'])) {
                        $uuid = $objectif['uuid'] ?? $objectif['id'];
                        $result[] = [
                            $uuid => [
                                'etat_valid' => $objectif['etat_valid'],
                                'valideur' => $objectif['valideur'] ?? null,
                                'commentaire' => $objectif['commentaire'] ?? null,
                                'date_validation' => $objectif['date_validation'] ?? null,
                                'date_proposition' => $objectif['date_proposition'] ?? null,
                            ]
                        ];
                    }
                }
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    /**
     * Tous les objectifs du parcours
     */
    public function objectifs_du_parcours(array $ppa = [], string $affectation = null, string $competence = null, string $savoirfaire = null)
    {
        $result = [];
        foreach ($this->savoirfaires_du_parcours($ppa, $affectation, $competence) as $csf) {
            if ($savoirfaire == null || $csf['uuid'] == $savoirfaire || $csf['id'] == $savoirfaire) {
                foreach ($csf['objectifs'] ?? [] as $objectif) {
                    $result[] = $objectif;
                }
            }
        }
        return $result;
    }

    // ========================================
    // OBJECTIFS PAR TYPE
    // ========================================

    /**
     * Objectifs de type STAGE validés
     */
    public function stages_valid_du_parcours(array $ppa = [], string $affectation = null, string $competence = null, string $savoirfaire = null)
    {
        return $this->objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return $objectif['etat_valid'] === true &&
                   ($objectif['type_objectif'] ?? '') === 'STAGE';
        }, $ppa);
    }

    /**
     * Objectifs de type STAGE non validés
     */
    public function stages_nonvalid_du_parcours(array $ppa = [], string $affectation = null, string $competence = null, string $savoirfaire = null)
    {
        return $this->objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return $objectif['etat_valid'] !== true &&
                   ($objectif['type_objectif'] ?? '') === 'STAGE';
        }, $ppa);
    }

    /**
     * Tous les objectifs de type STAGE
     */
    public function stages_du_parcours(array $ppa = [], string $affectation = null, string $competence = null, string $savoirfaire = null)
    {
        return $this->objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return ($objectif['type_objectif'] ?? '') === 'STAGE';
        }, $ppa);
    }

    /**
     * Objectifs de type TACHE validés
     */
    public function taches_valid_du_parcours(array $ppa = [], string $affectation = null, string $competence = null, string $savoirfaire = null)
    {
        return $this->objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return $objectif['etat_valid'] === true &&
                   ($objectif['type_objectif'] ?? '') === 'TACHE';
        }, $ppa);
    }

    /**
     * Objectifs de type TACHE non validés
     */
    public function taches_nonvalid_du_parcours(array $ppa = [], string $affectation = null, string $competence = null, string $savoirfaire = null)
    {
        return $this->objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return $objectif['etat_valid'] !== true &&
                   ($objectif['type_objectif'] ?? '') === 'TACHE';
        }, $ppa);
    }

    /**
     * Objectifs de type TACHE proposés
     */
    public function taches_propos_du_parcours(array $ppa = [], string $affectation = null, string $competence = null, string $savoirfaire = null)
    {
        return $this->objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return ($objectif['type_objectif'] ?? '') === 'TACHE' &&
                   !empty($objectif['date_proposition']);
        }, $ppa);
    }

    /**
     * Tous les objectifs de type TACHE
     */
    public function taches_du_parcours(array $ppa = [], string $affectation = null, string $competence = null, string $savoirfaire = null)
    {
        return $this->objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return ($objectif['type_objectif'] ?? '') === 'TACHE';
        }, $ppa);
    }

    // ========================================
    // SAVOIR-FAIRES (competenceSavoirfaires)
    // ========================================

    /**
     * Savoir-faires validés du parcours
     */
    public function savoirfaires_valid_du_parcours(array $ppa = [], string $affectation = null, string $competence = null)
    {
        $result = [];
        foreach ($this->competences_du_parcours($ppa, $affectation) as $comp) {
            if ($competence == null || $comp['uuid'] == $competence || $comp['id'] == $competence) {
                foreach ($comp['competenceSavoirfaires'] ?? [] as $csf) {
                    if ($csf['etat_valid'] === true) {
                        $uuid = $csf['uuid'] ?? $csf['id'];
                        $result[] = [
                            $uuid => [
                                'etat_valid' => $csf['etat_valid'],
                                'valideur' => $csf['valideur'] ?? null,
                                'commentaire' => $csf['commentaire'] ?? null,
                                'date_validation' => $csf['date_validation'] ?? null,
                                'date_proposition' => $csf['date_proposition'] ?? null,
                            ]
                        ];
                    }
                }
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    public function savoirfaires_valides_ou_proposes_du_parcours(array $ppa = [], string $affectation = null, string $competence = null)
    {
        $result = [];
        foreach ($this->competences_du_parcours($ppa, $affectation) as $comp) {
            if ($competence == null || $comp['uuid'] == $competence || $comp['id'] == $competence) {
                foreach ($comp['competenceSavoirfaires'] ?? [] as $csf) {
                    if ($csf['etat_valid'] === true || Arr::get($csf,'etat_propos') === true) {
                        $uuid = $csf['uuid'] ?? $csf['id'];
                        $result[] = [
                            $uuid => [
                                'etat_valid' => $csf['etat_valid'],
                                'etat_propos' => Arr::get($csf,'etat_propos', false),
                                'valideur' => $csf['valideur'] ?? null,
                                'commentaire' => $csf['commentaire'] ?? null,
                                'date_validation' => $csf['date_validation'] ?? null,
                                'date_proposition' => $csf['date_proposition'] ?? null,
                                'commentaire_proposition' => $csf['commentaire_proposition'] ?? null,
                            ]
                        ];
                    }
                }
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    /**
     * Savoir-faires non validés du parcours
     */
    public function savoirfaires_nonvalid_du_parcours(array $ppa = [], string $affectation = null, string $competence = null)
    {
        $result = [];
        foreach ($this->competences_du_parcours($ppa, $affectation) as $comp) {
            if ($competence == null || $comp['uuid'] == $competence || $comp['id'] == $competence) {
                foreach ($comp['competenceSavoirfaires'] ?? [] as $csf) {
                    if ($csf['etat_valid'] !== true) {
                        $uuid = $csf['uuid'] ?? $csf['id'];
                        $result[] = [
                            $uuid => [
                                'etat_valid' => $csf['etat_valid'],
                                'valideur' => $csf['valideur'] ?? null,
                                'commentaire' => $csf['commentaire'] ?? null,
                                'date_validation' => $csf['date_validation'] ?? null,
                                'date_proposition' => $csf['date_proposition'] ?? null,
                            ]
                        ];
                    }
                }
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    /**
     * Savoir-faires proposés du parcours
     */
    public function savoirfaires_propos_du_parcours(array $ppa = [], string $affectation = null, string $competence = null)
    {
        $result = [];
        foreach ($this->competences_du_parcours($ppa, $affectation) as $comp) {
            if ($competence == null || $comp['uuid'] == $competence || $comp['id'] == $competence) {
                foreach ($comp['competenceSavoirfaires'] ?? [] as $csf) {
                    if (!empty($csf['date_proposition'])) {
                        $uuid = $csf['uuid'] ?? $csf['id'];
                        $result[] = [
                            $uuid => [
                                'etat_valid' => $csf['etat_valid'],
                                'valideur' => $csf['valideur'] ?? null,
                                'commentaire' => $csf['commentaire'] ?? null,
                                'date_validation' => $csf['date_validation'] ?? null,
                                'date_proposition' => $csf['date_proposition'] ?? null,
                            ]
                        ];
                    }
                }
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    /**
     * Tous les savoir-faires du parcours
     */
    public function savoirfaires_du_parcours(array $ppa = [], string $affectation = null, string $competence = null)
    {
        $result = [];
        foreach ($this->competences_du_parcours($ppa, $affectation) as $comp) {
            if ($competence == null || $comp['uuid'] == $competence || $comp['id'] == $competence) {
                foreach ($comp['competenceSavoirfaires'] ?? [] as $csf) {
                    $result[] = $csf;
                }
            }
        }
        return $result;
    }

    // ========================================
    // COMPÉTENCES
    // ========================================

    /**
     * Compétences validées du parcours
     */
    public function competences_valid_du_parcours(array $ppa = [], string $affectation = null)
    {
        $result = [];
        foreach ($this->affectations_du_parcours($ppa) as $aff) {
            if ($affectation == null || $aff['uuid'] == $affectation || $aff['id'] == $affectation) {
                foreach ($aff['competences'] ?? [] as $competence) {
                    if ($competence['etat_valid'] === true) {
                        $result[] = ['uuid' => $competence['uuid'] ?? $competence['id']];
                    }
                }
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    public function competences_validees_ou_proposees_du_parcours(array $ppa = [], string $affectation = null)
    {
        $result = [];
        foreach ($this->affectations_du_parcours($ppa) as $aff) {
            if ($affectation == null || $aff['uuid'] == $affectation || $aff['id'] == $affectation) {
                foreach ($aff['competences'] ?? [] as $competence) {
                    if ($competence['etat_valid'] === true || Arr::get($competence,'etat_propos') === true) {
                        $result[] = ['uuid' => $competence['uuid'] ?? $competence['id']];
                    }
                }
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    /**
     * Compétences non validées du parcours
     */
    public function competences_nonvalid_du_parcours(array $ppa = [], string $affectation = null)
    {
        $result = [];
        foreach ($this->affectations_du_parcours($ppa) as $aff) {
            if ($affectation == null || $aff['uuid'] == $affectation || $aff['id'] == $affectation) {
                foreach ($aff['competences'] ?? [] as $competence) {
                    if ($competence['etat_valid'] !== true) {
                        $result[] = ['uuid' => $competence['uuid'] ?? $competence['id']];
                    }
                }
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    /**
     * Toutes les compétences du parcours
     */
    public function competences_du_parcours(array $ppa = [], string $affectation = null)
    {
        $result = [];
        foreach ($this->affectations_du_parcours($ppa) as $aff) {
            if ($affectation == null || $aff['uuid'] == $affectation || $aff['id'] == $affectation) {
                foreach ($aff['competences'] ?? [] as $competence) {
                    $result[] = $competence;
                }
            }
        }
        return $result;
    }

    // ========================================
    // AFFECTATIONS
    // ========================================

    /**
     * Affectations validées du parcours
     */
    public function affectations_valid_du_parcours(array $ppa = [])
    {
        $result = [];
        foreach ($ppa['affectations'] ?? [] as $affectation) {
            if ($affectation['etat_valid'] === true) {
                $result[] = ['uuid' => $affectation['uuid'] ?? $affectation['id']];
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    public function affectations_validees_ou_proposees_du_parcours(array $ppa = [])
    {
        $result = [];
        foreach ($ppa['affectations'] ?? [] as $affectation) {
            if ($affectation['etat_valid'] === true || Arr::get($affectation, 'etat_propos') === true) {
                $result[] = ['uuid' => $affectation['uuid'] ?? $affectation['id']];
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    /**
     * Affectations non validées du parcours
     */
    public function affectations_nonvalid_du_parcours(array $ppa = [])
    {
        $result = [];
        foreach ($ppa['affectations'] ?? [] as $affectation) {
            if ($affectation['etat_valid'] !== true) {
                $result[] = ['uuid' => $affectation['uuid'] ?? $affectation['id']];
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    /**
     * Toutes les affectations du parcours
     */
    public function affectations_du_parcours(array $ppa = [])
    {
        return $ppa['affectations'] ?? [];
    }
}
