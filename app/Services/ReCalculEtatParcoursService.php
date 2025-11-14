<?php
// filepath: Modules/FcmCommun/Services/ReCalculEtatParcoursService.php

namespace Modules\FcmCommun\Services;

use Illuminate\Support\Facades\Log;

class CalculHeuresParcoursService
{
    /**
     * Point d'entrée principal pour mettre à jour toutes les heures dans le JSON
     */
    public static function mettreAJourHeures(array &$parcoursMarin): bool
    {
        return (new self())->mettreAJourHeuresDansJson($parcoursMarin);
    }

    /**
     * ✅ Mise à jour des heures totales par affectation
     */
    public function mettreAJourHeuresDansJson(array &$parcoursMarin): bool
    {
        try {
            if (!$this->validerStructure($parcoursMarin)) {
                return false;
            }

            foreach ($parcoursMarin['affectations'] as &$affectation) {
                $heuresValidees = 0;
                $heuresNonValidees = 0;
                $heuresTotal = 0;

                foreach ($affectation['competences'] ?? [] as $competence) {
                    foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                        $heures = $this->getHeures($csf);
                        $heuresTotal += $heures;

                        if ($csf['etat_valid'] ?? false) {
                            $heuresValidees += $heures;
                        } else {
                            $heuresNonValidees += $heures;
                        }
                    }
                }

                // ✅ Mise à jour dans l'affectation
                $affectation['heures_total'] = round($heuresTotal, 2);
                $affectation['heures_validees'] = round($heuresValidees, 2);
                $affectation['heures_non_validees'] = round($heuresNonValidees, 2);
                $affectation['taux_heures'] = $heuresTotal > 0
                    ? round(($heuresValidees / $heuresTotal) * 100, 2)
                    : 0;

                Log::info('Heures calculées pour affectation', [
                    'uuid' => $affectation['uuid'] ?? 'inconnu',
                    'total' => $heuresTotal,
                    'validees' => $heuresValidees,
                    'taux' => $affectation['taux_heures']
                ]);
            }
            unset($affectation);

            // ✅ Calcul global du parcours
            $parcoursMarin['heures_total'] = $this->calculerHeuresTotal($parcoursMarin);
            $parcoursMarin['heures_validees'] = $this->calculerHeuresValidees($parcoursMarin);
            $parcoursMarin['heures_non_validees'] = $parcoursMarin['heures_total'] - $parcoursMarin['heures_validees'];
            $parcoursMarin['taux_heures'] = $parcoursMarin['heures_total'] > 0
                ? round(($parcoursMarin['heures_validees'] / $parcoursMarin['heures_total']) * 100, 2)
                : 0;

            return true;

        } catch (\Exception $e) {
            Log::error('Erreur calcul heures parcours', ['erreur' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * ✅ Calcule le total des heures du parcours
     */
    public function calculerHeuresTotal(array $parcoursMarin): float
    {
        $total = 0;

        foreach ($parcoursMarin['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    $total += $this->getHeures($csf);
                }
            }
        }

        return round($total, 2);
    }

    /**
     * ✅ Calcule les heures validées du parcours
     */
    public function calculerHeuresValidees(array $parcoursMarin): float
    {
        $validees = 0;

        foreach ($parcoursMarin['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    if ($csf['etat_valid'] ?? false) {
                        $validees += $this->getHeures($csf);
                    }
                }
            }
        }

        return round($validees, 2);
    }

    /**
     * ✅ Récupère les heures d'un competenceSavoirfaire (pivot)
     */
    private function getHeures(array $csf): float
    {
        // Priorité : heures directement dans le CSF
        if (isset($csf['heures']) && is_numeric($csf['heures'])) {
            return (float) $csf['heures'];
        }

        // Si pas d'heures : essayer de calculer depuis jours (1 jour = 7h)
        if (isset($csf['jours']) && is_numeric($csf['jours'])) {
            return (float) $csf['jours'] * 7;
        }

        // Par défaut : 0
        return 0;
    }

    /**
     * ✅ Valide la structure JSON (affectations > competences > competenceSavoirfaires)
     */
    private function validerStructure(array $parcoursMarin): bool
    {
        if (!isset($parcoursMarin['affectations']) || !is_array($parcoursMarin['affectations'])) {
            Log::error('Structure JSON invalide: affectations manquantes');
            return false;
        }

        return true;
    }

    /**
     * ✅ Calcule tous les indicateurs heures en une fois
     */
    public function calculerTousIndicateursHeures(array $parcoursMarin): array
    {
        $heuresTotal = $this->calculerHeuresTotal($parcoursMarin);
        $heuresValidees = $this->calculerHeuresValidees($parcoursMarin);

        return [
            'heures_total' => $heuresTotal,
            'heures_validees' => $heuresValidees,
            'heures_non_validees' => $heuresTotal - $heuresValidees,
            'taux_heures' => $heuresTotal > 0
                ? round(($heuresValidees / $heuresTotal) * 100, 2)
                : 0,
        ];
    }
}
