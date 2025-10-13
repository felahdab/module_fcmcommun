<?php
// filepath: Modules/FcmCommun/Services/CalculTauxParcoursService.php

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
            $parcoursMarin['taux_activite'] = $this->calculerTauxObjectifs($parcoursMarin);
           
            return true;
           
        } catch (\Exception $e) {
            Log::error('Erreur calcul taux parcours', ['erreur' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * ✅ Calcul du % global d'avancement (STRUCTURE annees > savoirfaires > objectifs)
     */
    public function calculerTauxGlobal(array $parcoursMarin): float
    {
        $totcoef = 0;
        $totvalid = 0;
        $list_sfs = $this->extraireSavoirfairesDuJson($parcoursMarin);

        foreach ($list_sfs as $sf) {
            $objectifs = $this->getObjectifsPourSavoirfaire($sf);
            $nbObjectifs = count($objectifs);
           
            // ✅ Si 0 objectifs dans le SF => on prend le coef du SF
            if ($nbObjectifs == 0) {
                $coeff = $this->getCoeff($sf);
                $totcoef += $coeff;
                if ($sf['etat_valid'] == true) {
                    $totvalid += $coeff;
                }
            }
            // ✅ Si >0 objectifs dans le SF => on prend le total des coeff des objectifs
            else {
                foreach ($objectifs as $objectif) {
                    $coeff = $this->getCoeff($objectif);
                    $totcoef += $coeff;
                    if ($objectif['etat_valid'] == true) {
                        $totvalid += $coeff;
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
     * ✅ Calcul du % d'avancement des STAGES uniquement (savoirfaireType.code == 'S')
     */
    public function calculerTauxStages(array $parcoursMarin): float
    {
        $totcoef = 0;
        $totvalid = 0;
        $list_sfs = $this->extraireSavoirfairesDuJson($parcoursMarin);

        foreach ($list_sfs as $sf) {
            // ✅ Filtrer uniquement les STAGES (savoirfaireType.code == 'S')
            if ($this->getSavoirfaireTypeCode($sf) === 'S') {
                $objectifs = $this->getObjectifsPourSavoirfaire($sf);
               
                if (empty($objectifs)) {
                    // SF stage sans objectifs
                    $coeff = $this->getCoeff($sf);
                    $totcoef += $coeff;
                    if ($sf['etat_valid'] == true) {
                        $totvalid += $coeff;
                    }
                } else {
                    // SF stage avec objectifs
                    foreach ($objectifs as $objectif) {
                        $coeff = $this->getCoeff($objectif);
                        $totcoef += $coeff;
                        if ($objectif['etat_valid'] == true) {
                            $totvalid += $coeff;
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
     * ✅ Calcul du % d'avancement COMPAGNONNAGE (savoirfaireType.code == 'C')
     */
    public function calculerTauxObjectifs(array $parcoursMarin): float
    {
        $totcoef = 0;
        $totvalid = 0;
        $list_sfs = $this->extraireSavoirfairesDuJson($parcoursMarin);

        foreach ($list_sfs as $sf) {
            // ✅ Filtrer uniquement le COMPAGNONNAGE (savoirfaireType.code == 'C')
            if ($this->getSavoirfaireTypeCode($sf) === 'C') {
                $objectifs = $this->getObjectifsPourSavoirfaire($sf);
               
                if (empty($objectifs)) {
                    // SF compagnonnage sans objectifs
                    $coeff = $this->getCoeff($sf);
                    $totcoef += $coeff;
                    if ($sf['etat_valid'] == true) {
                        $totvalid += $coeff;
                    }
                } else {
                    // SF compagnonnage avec objectifs
                    foreach ($objectifs as $objectif) {
                        $coeff = $this->getCoeff($objectif);
                        $totcoef += $coeff;
                        if ($objectif['etat_valid'] == true) {
                            $totvalid += $coeff;
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
     * ✅ Récupère le code du type de savoir-faire
     */
    private function getSavoirfaireTypeCode(array $savoirfaire): ?string
    {
        return $savoirfaire['savoirfaire_type']['code'] ??
               $savoirfaire['savoirfaireType']['code'] ??
               $savoirfaire['type_code'] ??
               null;
    }

    /**
     * ✅ Récupère le coefficient d'un élément
     */
    private function getCoeff(array $element): float
    {
        return (float)($element['coeff'] ?? $element['pivot']['coeff'] ?? 1);
    }

    /**
     * ✅ Récupère les objectifs d'un savoir-faire
     */
    private function getObjectifsPourSavoirfaire(array $savoirfaire): array
    {
        // ✅ Objectifs directement dans le savoir-faire
        if (isset($savoirfaire['objectifs']) && is_array($savoirfaire['objectifs'])) {
            return $savoirfaire['objectifs'];
        }

        // ✅ Objectifs via pivot ID (chargement depuis la base)
        $pivotId = $savoirfaire['pivot']['id'] ?? null;
        if ($pivotId) {
            try {
                $anneeSavoirfaire = \Modules\FcmCentral\Models\AnneeSavoirfaire::with('objectifs')
                    ->find($pivotId);
               
                if ($anneeSavoirfaire && $anneeSavoirfaire->objectifs) {
                    return $anneeSavoirfaire->objectifs->map(function ($objectif) {
                        return [
                            'id' => $objectif->id,
                            'uuid' => $objectif->id,
                            'coeff' => $objectif->coeff ?? 1,
                            'etat_valid' => false, // ✅ À adapter selon ta logique de suivi
                        ];
                    })->toArray();
                }
            } catch (\Exception $e) {
                Log::warning("Erreur récupération objectifs pour pivot {$pivotId}: " . $e->getMessage());
            }
        }

        return [];
    }

    /**
     * ✅ Extrait la liste des savoir-faires du JSON (STRUCTURE UNIQUE)
     */
    private function extraireSavoirfairesDuJson(array $parcoursMarin): array
    {
        $list_sfs = [];

        foreach ($parcoursMarin['annees'] as $annee) {
            if (isset($annee['savoirfaires'])) {
                foreach ($annee['savoirfaires'] as $savoirFaire) {
                    $list_sfs[] = $savoirFaire;
                }
            }
        }

        return $list_sfs;
    }

    /**
     * ✅ Valide la structure JSON (STRUCTURE UNIQUE)
     */
    private function validerStructure(array $parcoursMarin): bool
    {
        if (!isset($parcoursMarin['annees']) || !is_array($parcoursMarin['annees'])) {
            Log::error('Structure JSON invalide: annees manquantes');
            return false;
        }
       
        return true;
    }

    /**
     * ✅ Calcule tous les taux en une fois
     */
    public function calculerTousTaux(array $parcoursMarin): array
    {
        return [
            'taux_global' => $this->calculerTauxGlobal($parcoursMarin),
            'taux_stages' => $this->calculerTauxStages($parcoursMarin),
            'taux_compagnonnage' => $this->calculerTauxObjectifs($parcoursMarin),
        ];
    }
}
