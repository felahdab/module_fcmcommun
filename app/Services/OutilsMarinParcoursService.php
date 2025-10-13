<?php
// filepath: Modules/FcmCommun/Services/OutilsMarinParcoursService.php

namespace Modules\FcmCommun\Services;

use Closure;

class OutilsMarinParcoursService
{
    public $ppa = null;
    public $annee = null;
    public $savoirfaire = null;
    public $objectif = null;

    // ========================================
    // MÉTHODES GÉNÉRIQUES AVEC CLOSURE
    // ========================================

    /**
     * Retourne tous les objectifs du parcours respectant une condition
     */
    public function objectifs_du_parcours_respectant_une_condition(Closure $predicat, array $ppa = [])
    {
        $result = [];
        foreach ($ppa["annees"] ?? [] as $annee) {
            foreach ($annee["savoirfaires"] ?? [] as $savoirfaire) {
                foreach ($savoirfaire["objectifs"] ?? [] as $objectif) {
                    if ($predicat($objectif)) {
                        $result[] = $objectif;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Retourne tous les savoir-faires du parcours respectant une condition
     */
    public function savoirfaires_du_parcours_respectant_une_condition(Closure $predicat, array $ppa = [])
    {
        $result = [];
        foreach ($ppa["annees"] ?? [] as $annee) {
            foreach ($annee["savoirfaires"] ?? [] as $savoirfaire) {
                if ($predicat($savoirfaire)) {
                    $result[] = $savoirfaire;
                }
            }
        }
        return $result;
    }

    /**
     * Retourne toutes les années du parcours respectant une condition
     */
    public function annees_du_parcours_respectant_une_condition(Closure $predicat, array $ppa = [])
    {
        $result = [];
        foreach ($ppa["annees"] ?? [] as $annee) {
            if ($predicat($annee)) {
                $result[] = $annee;
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
    public function liste_objectifs_valid_du_parcours(array $ppa = [], string $annee = null, string $savoirfaire = null)
    {
        return $this->liste_des_objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return $objectif["etat_valid"] === true;
        }, $ppa);
    }

    /**
     * Objectifs validés du parcours avec détails
     */
    public function objectifs_valid_du_parcours(array $ppa = [], string $annee = null, string $savoirfaire = null)
    {
        $result = [];
        foreach ($this->savoirfaires_du_parcours($ppa, $annee) as $sf) {
            if ($savoirfaire == null || $sf['uuid'] == $savoirfaire || $sf['id'] == $savoirfaire) {
                foreach ($sf['objectifs'] ?? [] as $objectif) {
                    if ($objectif['etat_valid'] === true) {
                        $uuid = $objectif['uuid'] ?? $objectif['id'];
                        $result[] = [
                            $uuid => [
                                'etat_valid' => $objectif['etat_valid'],
                                'valideur' => $objectif['valideur'] ?? null,
                                'commentaire' => $objectif['commentaire'] ?? null,
                                'date_validation' => $objectif['date_validation'] ?? null,
                                'commentaire_proposition' => $objectif['commentaire_proposition'] ?? null,
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
    public function objectifs_nonvalid_du_parcours(array $ppa = [], string $annee = null, string $savoirfaire = null)
    {
        $result = [];
        foreach ($this->savoirfaires_du_parcours($ppa, $annee) as $sf) {
            if ($savoirfaire == null || $sf['uuid'] == $savoirfaire || $sf['id'] == $savoirfaire) {
                foreach ($sf['objectifs'] ?? [] as $objectif) {
                    if ($objectif['etat_valid'] !== true) {
                        $uuid = $objectif['uuid'] ?? $objectif['id'];
                        $result[] = [
                            $uuid => [
                                'etat_valid' => $objectif['etat_valid'],
                                'valideur' => $objectif['valideur'] ?? null,
                                'commentaire' => $objectif['commentaire'] ?? null,
                                'date_validation' => $objectif['date_validation'] ?? null,
                                'commentaire_proposition' => $objectif['commentaire_proposition'] ?? null,
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
    public function objectifs_propos_du_parcours(array $ppa = [], string $annee = null, string $savoirfaire = null)
    {
        $result = [];
        foreach ($this->savoirfaires_du_parcours($ppa, $annee) as $sf) {
            if ($savoirfaire == null || $sf['uuid'] == $savoirfaire || $sf['id'] == $savoirfaire) {
                foreach ($sf['objectifs'] ?? [] as $objectif) {
                    if (!empty($objectif['date_proposition'])) {
                        $uuid = $objectif['uuid'] ?? $objectif['id'];
                        $result[] = [
                            $uuid => [
                                'etat_valid' => $objectif['etat_valid'],
                                'valideur' => $objectif['valideur'] ?? null,
                                'commentaire' => $objectif['commentaire'] ?? null,
                                'date_validation' => $objectif['date_validation'] ?? null,
                                'commentaire_proposition' => $objectif['commentaire_proposition'] ?? null,
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
    public function objectifs_du_parcours(array $ppa = [], string $annee = null, string $savoirfaire = null)
    {
        $result = [];
        foreach ($this->savoirfaires_du_parcours($ppa, $annee) as $sf) {
            if ($savoirfaire == null || $sf['uuid'] == $savoirfaire || $sf['id'] == $savoirfaire) {
                foreach ($sf['objectifs'] ?? [] as $objectif) {
                    $uuid = $objectif['uuid'] ?? $objectif['id'];
                    $result[] = [
                        $uuid => [
                            'etat_valid' => $objectif['etat_valid'],
                            'valideur' => $objectif['valideur'] ?? null,
                            'commentaire' => $objectif['commentaire'] ?? null,
                            'date_validation' => $objectif['date_validation'] ?? null,
                            'commentaire_proposition' => $objectif['commentaire_proposition'] ?? null,
                            'date_proposition' => $objectif['date_proposition'] ?? null,
                        ]
                    ];
                }
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    // ========================================
    // OBJECTIFS PAR TYPE
    // ========================================

    /**
     * Objectifs de type STAGE validés
     */
    public function stages_valid_du_parcours(array $ppa = [], string $annee = null, string $savoirfaire = null)
    {
        return $this->objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return $objectif['etat_valid'] === true && 
                   ($objectif['type_objectif'] ?? '') === 'STAGE';
        }, $ppa);
    }

    /**
     * Objectifs de type STAGE non validés
     */
    public function stages_nonvalid_du_parcours(array $ppa = [], string $annee = null, string $savoirfaire = null)
    {
        return $this->objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return $objectif['etat_valid'] !== true && 
                   ($objectif['type_objectif'] ?? '') === 'STAGE';
        }, $ppa);
    }

    /**
     * Tous les objectifs de type STAGE
     */
    public function stages_du_parcours(array $ppa = [], string $annee = null, string $savoirfaire = null)
    {
        return $this->objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return ($objectif['type_objectif'] ?? '') === 'STAGE';
        }, $ppa);
    }

    /**
     * Objectifs de type TACHE validés
     */
    public function taches_valid_du_parcours(array $ppa = [], string $annee = null, string $savoirfaire = null)
    {
        return $this->objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return $objectif['etat_valid'] === true && 
                   ($objectif['type_objectif'] ?? '') === 'TACHE';
        }, $ppa);
    }

    /**
     * Objectifs de type TACHE non validés
     */
    public function taches_nonvalid_du_parcours(array $ppa = [], string $annee = null, string $savoirfaire = null)
    {
        return $this->objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return $objectif['etat_valid'] !== true && 
                   ($objectif['type_objectif'] ?? '') === 'TACHE';
        }, $ppa);
    }

    /**
     * Objectifs de type TACHE proposés
     */
    public function taches_propos_du_parcours(array $ppa = [], string $annee = null, string $savoirfaire = null)
    {
        return $this->objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return ($objectif['type_objectif'] ?? '') === 'TACHE' && 
                   !empty($objectif['date_proposition']);
        }, $ppa);
    }

    /**
     * Tous les objectifs de type TACHE
     */
    public function taches_du_parcours(array $ppa = [], string $annee = null, string $savoirfaire = null)
    {
        return $this->objectifs_du_parcours_respectant_une_condition(function ($objectif) {
            return ($objectif['type_objectif'] ?? '') === 'TACHE';
        }, $ppa);
    }

    // ========================================
    // SAVOIR-FAIRES
    // ========================================

    /**
     * Savoir-faires validés du parcours
     */
    public function savoirfaires_valid_du_parcours(array $ppa = [], string $annee = null)
    {
        $result = [];
        foreach ($this->annees_du_parcours($ppa) as $ann) {
            if ($annee == null || $ann['uuid'] == $annee || $ann['id'] == $annee) {
                foreach ($ann['savoirfaires'] ?? [] as $savoirfaire) {
                    if ($savoirfaire['etat_valid'] === true) {
                        $uuid = $savoirfaire['uuid'] ?? $savoirfaire['id'];
                        $result[] = [
                            $uuid => [
                                'etat_valid' => $savoirfaire['etat_valid'],
                                'valideur' => $savoirfaire['valideur'] ?? null,
                                'commentaire' => $savoirfaire['commentaire'] ?? null,
                                'date_validation' => $savoirfaire['date_validation'] ?? null,
                                'commentaire_proposition' => $savoirfaire['commentaire_proposition'] ?? null,
                                'date_proposition' => $savoirfaire['date_proposition'] ?? null,
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
    public function savoirfaires_nonvalid_du_parcours(array $ppa = [], string $annee = null)
    {
        $result = [];
        foreach ($this->annees_du_parcours($ppa) as $ann) {
            if ($annee == null || $ann['uuid'] == $annee || $ann['id'] == $annee) {
                foreach ($ann['savoirfaires'] ?? [] as $savoirfaire) {
                    if ($savoirfaire['etat_valid'] !== true) {
                        $uuid = $savoirfaire['uuid'] ?? $savoirfaire['id'];
                        $result[] = [
                            $uuid => [
                                'etat_valid' => $savoirfaire['etat_valid'],
                                'valideur' => $savoirfaire['valideur'] ?? null,
                                'commentaire' => $savoirfaire['commentaire'] ?? null,
                                'date_validation' => $savoirfaire['date_validation'] ?? null,
                                'commentaire_proposition' => $savoirfaire['commentaire_proposition'] ?? null,
                                'date_proposition' => $savoirfaire['date_proposition'] ?? null,
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
    public function savoirfaires_propos_du_parcours(array $ppa = [], string $annee = null)
    {
        $result = [];
        foreach ($this->annees_du_parcours($ppa) as $ann) {
            if ($annee == null || $ann['uuid'] == $annee || $ann['id'] == $annee) {
                foreach ($ann['savoirfaires'] ?? [] as $savoirfaire) {
                    if (!empty($savoirfaire['date_proposition'])) {
                        $uuid = $savoirfaire['uuid'] ?? $savoirfaire['id'];
                        $result[] = [
                            $uuid => [
                                'etat_valid' => $savoirfaire['etat_valid'],
                                'valideur' => $savoirfaire['valideur'] ?? null,
                                'commentaire' => $savoirfaire['commentaire'] ?? null,
                                'date_validation' => $savoirfaire['date_validation'] ?? null,
                                'commentaire_proposition' => $savoirfaire['commentaire_proposition'] ?? null,
                                'date_proposition' => $savoirfaire['date_proposition'] ?? null,
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
    public function savoirfaires_du_parcours(array $ppa = [], string $annee = null)
    {
        $result = [];
        foreach ($this->annees_du_parcours($ppa) as $ann) {
            if ($annee == null || $ann['uuid'] == $annee || $ann['id'] == $annee) {
                foreach ($ann['savoirfaires'] ?? [] as $savoirfaire) {
                    $result[] = $savoirfaire;
                }
            }
        }
        return $result;
    }

    // ========================================
    // ANNÉES
    // ========================================

    /**
     * Années validées du parcours
     */
    public function annees_valid_du_parcours(array $ppa = [])
    {
        $result = [];
        foreach ($ppa['annees'] ?? [] as $annee) {
            if ($annee['etat_valid'] === true) {
                $result[] = ['uuid' => $annee['uuid'] ?? $annee['id']];
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    /**
     * Années non validées du parcours
     */
    public function annees_nonvalid_du_parcours(array $ppa = [])
    {
        $result = [];
        foreach ($ppa['annees'] ?? [] as $annee) {
            if ($annee['etat_valid'] !== true) {
                $result[] = ['uuid' => $annee['uuid'] ?? $annee['id']];
            }
        }
        return array_unique($result, SORT_REGULAR);
    }

    /**
     * Toutes les années du parcours
     */
    public function annees_du_parcours(array $ppa = [])
    {
        return $ppa['annees'] ?? [];
    }
}
