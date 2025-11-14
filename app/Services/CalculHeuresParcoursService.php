<?php
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
     * ✅ Mise à jour des heures totales par affectation + taux global
     */
    public function mettreAJourHeuresDansJson(array &$parcoursMarin): bool
    {
        try {
            if (!$this->validerStructure($parcoursMarin)) {
                return false;
            }

            $heuresGlobalTotal = 0;
            $heuresGlobalValidees = 0;

            // ✅ CALCUL PAR AFFECTATION
            foreach ($parcoursMarin['affectations'] as $affKey => &$affectation) {
                $heuresTotal = 0;
                $heuresValidees = 0;

                foreach ($affectation['competences'] ?? [] as $competence) {
                    foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                        $heures = $this->getHeures($csf);
                        $heuresTotal += $heures;

                        // ✅ Si le CSF est validé → cumuler dans heures validées
                        if ($csf['etat_valid'] ?? false) {
                            $heuresValidees += $heures;
                        }
                    }
                }

                // ✅ Mise à jour dans l'affectation
                $affectation['heures_total'] = round($heuresTotal, 2);
                $affectation['heures_validees'] = round($heuresValidees, 2);
                $affectation['heures_restantes'] = round($heuresTotal - $heuresValidees, 2);
                $affectation['taux_heures'] = $heuresTotal > 0
                    ? round(($heuresValidees / $heuresTotal) * 100, 2)
                    : 0;

                // ✅ Cumul pour le taux global
                $heuresGlobalTotal += $heuresTotal;
                $heuresGlobalValidees += $heuresValidees;

                Log::info("Heures calculées pour affectation {$affKey}", [
                    'uuid' => $affectation['uuid'] ?? 'inconnu',
                    'heures_total' => $heuresTotal,
                    'heures_validees' => $heuresValidees,
                    'taux' => $affectation['taux_heures']
                ]);
            }
            unset($affectation);

            // ✅ CALCUL DU TAUX GLOBAL (tout le parcours)
            $parcoursMarin['taux_global'] = $heuresGlobalTotal > 0
                ? round(($heuresGlobalValidees / $heuresGlobalTotal) * 100, 2)
                : 0;

            Log::info("Taux global calculé", [
                'heures_total' => $heuresGlobalTotal,
                'heures_validees' => $heuresGlobalValidees,
                'taux_global' => $parcoursMarin['taux_global']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Erreur calcul heures parcours', ['erreur' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * ✅ Récupère les heures d'un competenceSavoirfaire (pivot)
     */
    private function getHeures(array $csf): float
    {
        // ✅ Priorité 1 : heures directement dans le CSF
        if (isset($csf['heures']) && is_numeric($csf['heures'])) {
            return (float) $csf['heures'];
        }

        // ✅ Priorité 2 : jours (1 jour = 7h)
        if (isset($csf['jours']) && is_numeric($csf['jours'])) {
            return (float) $csf['jours'] * 7;
        }

        // ✅ Par défaut : 0
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
}
