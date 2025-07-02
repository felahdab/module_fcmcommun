<?php

namespace Modules\FcmCommun\Services;

use Illuminate\Support\Facades\Log;

class CalculTauxParcoursService
{
    /**
     * Point d'entrée principal pour mettre à jour tous les taux dans le JSON
     */

     public static function mettreAJourTaux(array &$parcoursMarin): bool
     {
        return (new self())->mettreAJourTauxDansJson($parcoursMarin);
     }
     
    public function mettreAJourTauxDansJson(array &$parcoursMarin): bool
    {
        try {
            if (!$this->validerStructure($parcoursMarin)) {
                return false;
            }

            $parcoursMarin['taux_global'] = $this->calculerTauxGlobal($parcoursMarin);
            $parcoursMarin['taux_stage'] = $this->calculerTauxStages($parcoursMarin);
            $parcoursMarin['taux_activite'] = $this->calculerTauxActivites($parcoursMarin);
            //$parcoursMarin['date_maj_taux'] = now()->toDateTimeString();
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Erreur calcul taux parcours', ['erreur' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Calcul du % global d'avancement 
     */
    public function calculerTauxGlobal(array $parcoursMarin): float
    {
        $totcoef = 0;
        $totvalid = 0;
        $list_sfs = $this->extraireSavoirfairesDuJson($parcoursMarin);

        foreach ($list_sfs as $sf) {
            $nbactivites = count($sf['activites']);
            
            // Si 0 activites dans le SF => on prend le coef du SF
            if ($nbactivites == 0) {
                $totcoef += $sf['coeff'];
                if ($sf['etat_valid'] == true) {
                    $totvalid += $sf['coeff'];
                }
            }
            // Si >0 activites dans le SF => on prend le total des coeff des activites
            else {
                foreach ($sf['activites'] as $activite) {
                    $totcoef += $activite['pivot']['coeff'];
                    if ($activite['etat_valid'] == true) {
                        $totvalid += $activite['pivot']['coeff'];
                    }
                }
            }
        }

        if ($totcoef == 0) {
            return 0;
        }

        return round($totvalid * 100 / $totcoef, 2);
    }

    /**
     * Calcul du % d'avancement des stages uniquement
     */
    public function calculerTauxStages(array $parcoursMarin): float
    {
        $totcoef = 0;
        $totvalid = 0;
        $list_sfs = $this->extraireSavoirfairesDuJson($parcoursMarin);

        foreach ($list_sfs as $sf) {
            foreach ($sf['activites'] as $activite) {
                // Filtrer uniquement les stages
                if (($activite['type_activite'] ?? '') === 'STAGE') {
                    $totcoef += $activite['pivot']['coeff'];
                    if ($activite['etat_valid'] == true) {
                        $totvalid += $activite['pivot']['coeff'];
                    }
                }
            }
        }

        if ($totcoef == 0) {
            return 0;
        }

        return round($totvalid * 100 / $totcoef, 2);
    }

    /**
     * Calcul du % d'avancement des activités (hors stages)
     */
    public function calculerTauxActivites(array $parcoursMarin): float
    {
        $totcoef = 0;
        $totvalid = 0;
        $list_sfs = $this->extraireSavoirfairesDuJson($parcoursMarin);

        foreach ($list_sfs as $sf) {
            $nbactivites = count($sf['activites']);
            
            // Si pas d'activités, on compte le SF comme une "activité"
            if ($nbactivites == 0) {
                $totcoef += $sf['coeff'];
                if ($sf['etat_valid'] == true) {
                    $totvalid += $sf['coeff'];
                }
            } else {
                foreach ($sf['activites'] as $activite) {
                    // Filtrer les activités (pas les stages)
                    if (($activite['type_activite'] ?? '') !== 'STAGE') {
                        $totcoef += $activite['pivot']['coeff'];
                        if ($activite['etat_valid'] == true) {
                            $totvalid += $activite['pivot']['coeff'];
                        }
                    }
                }
            }
        }

        if ($totcoef == 0) {
            return 0;
        }

        return round($totvalid * 100 / $totcoef, 2);
    }

    /**
     * Extrait la liste des savoir-faires du JSON (équivalent de savoirfaires_marin())
     */
    private function extraireSavoirfairesDuJson(array $parcoursMarin): array
    {
        $list_sfs = [];

        foreach ($parcoursMarin['fonctions'] as $fonction) {
            if (isset($fonction['competences'])) {
                foreach ($fonction['competences'] as $competence) {
                    if (isset($competence['savoirfaires'])) {
                        foreach ($competence['savoirfaires'] as $savoirFaire) {
                            // Ajouter les activités vides si pas présentes
                            if (!isset($savoirFaire['activites'])) {
                                $savoirFaire['activites'] = [];
                            }
                            
                            $list_sfs[] = $savoirFaire;
                        }
                    }
                }
            }
        }

        return $list_sfs;
    }

    /**
     * Valide la structure JSON
     */
    private function validerStructure(array $parcoursMarin): bool
    {
        if (!isset($parcoursMarin['fonctions']) || !is_array($parcoursMarin['fonctions'])) {
            Log::warning('Structure JSON invalide: fonctions manquantes');
            return false;
        }
        
        return true;
    }

    /**
     * Calcule tous les taux en une fois (pour optimisation)
     */
    public function calculerTousTaux(array $parcoursMarin): array
    {
        return [
            'taux_global' => $this->calculerTauxGlobal($parcoursMarin),
            'taux_stages' => $this->calculerTauxStages($parcoursMarin),
            'taux_activites' => $this->calculerTauxActivites($parcoursMarin),
        ];
    }
}
