<?php

namespace Modules\FcmCommun\Services;

use Illuminate\Support\Facades\Log;

class RefaireEtatParcoursService
{
    public static function refonte(array &$parcoursMarin): void
    {
        if (empty($parcoursMarin['affectations']) || !is_array($parcoursMarin['affectations'])) {
            return;
        }

        Log::info("=== DÉBUT RECALCUL ÉTATS ===");

        // ⚠️ UTILISER DES INDEX NUMÉRIQUES AU LIEU DE RÉFÉRENCES
        foreach ($parcoursMarin['affectations'] as $affIndex => &$affectation) {
            if (empty($affectation['competences']) || !is_array($affectation['competences'])) {
                $affectation['etat_valid'] = false;
                continue;
            }

            $affectationValide = true;

            foreach ($affectation['competences'] as $compIndex => &$competence) {
                if (empty($competence['competenceSavoirfaires']) || !is_array($competence['competenceSavoirfaires'])) {
                    $competence['etat_valid'] = false;
                    $affectationValide = false;
                    continue;
                }

                $competenceValide = true;

                // ✅ ITÉRATION PAR INDEX POUR ÉVITER LES RÉFÉRENCES CIRCULAIRES
                $nbSavoirfaires = count($competence['competenceSavoirfaires']);
                for ($sfIndex = 0; $sfIndex < $nbSavoirfaires; $sfIndex++) {
                    $csfValide = self::calculerEtatSavoirFaire(
                        $competence['competenceSavoirfaires'][$sfIndex]
                    );
                   
                    $parcoursMarin['affectations'][$affIndex]['competences'][$compIndex]['competenceSavoirfaires'][$sfIndex]['etat_valid'] = $csfValide;

                    if (!$csfValide) {
                        $competenceValide = false;
                    }

                    $sfCode = $competence['competenceSavoirfaires'][$sfIndex]['savoirfaire']['code'] ?? 'N/A';
                    Log::debug("SF {$sfCode}: " . ($csfValide ? 'VALIDE' : 'NON VALIDE'));
                }

                $competence['etat_valid'] = $competenceValide;

                if (!$competenceValide) {
                    $affectationValide = false;
                }

                Log::info("Compétence {$competence['code']}: " . ($competenceValide ? 'VALIDE' : 'NON VALIDE'));
            }

            $affectation['etat_valid'] = $affectationValide;
            Log::info("Affectation {$affectation['libelle_court']}: " . ($affectationValide ? 'VALIDE' : 'NON VALIDE'));
        }

        Log::info("=== FIN RECALCUL ÉTATS ===");
    }

    private static function calculerEtatSavoirFaire(array $savoirFaire): bool
    {
        $objetifsPresents = !empty($savoirFaire['objectifs']) && is_array($savoirFaire['objectifs']);
        $etatActuel = $savoirFaire['etat_valid'] ?? false;

        if ($objetifsPresents) {
            foreach ($savoirFaire['objectifs'] as $objectif) {
                if (!($objectif['etat_valid'] ?? false)) {
                    return false;
                }
            }
            return true;
        }

        return $etatActuel;
    }
}

