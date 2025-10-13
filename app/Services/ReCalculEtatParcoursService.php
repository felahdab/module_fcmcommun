<?php
// filepath: Modules/FcmCommun/Services/ReCalculEtatParcoursService.php

namespace Modules\FcmCommun\Services;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReCalculEtatParcoursService
{
    /**
     * Point d'entrée principal pour recalculer tous les états de validation
     */
    public static function recalculerEtats(array &$parcoursMarin): void
    {
        $service = new self();
        $service->recalculerTousLesEtats($parcoursMarin);
    }

    /**
     * ✅ Recalcule tous les états en remontant la hiérarchie (STRUCTURE UNIQUE)
     */
    public function recalculerTousLesEtats(array &$parcoursMarin): void
    {
        foreach ($parcoursMarin['annees'] as &$annee) {
            if (isset($annee['savoirfaires'])) {
                foreach ($annee['savoirfaires'] as &$savoirFaire) {
                    // ✅ 1. RECALCULER LE SAVOIR-FAIRE basé sur ses OBJECTIFS
                    $this->recalculerSavoirfaireAvecObjectifs($savoirFaire);
                }

                // ✅ 2. RECALCULER L'ANNÉE basée sur ses SAVOIR-FAIRES
                $this->validerSiTousEnfantsSontCoches($annee, 'savoirfaires');
            }
        }
    }

    /**
     * ✅ Recalculer un savoir-faire basé sur ses objectifs
     */
    private function recalculerSavoirfaireAvecObjectifs(array &$savoirFaire): void
    {
        $objectifs = $this->getObjectifsPourRecalcul($savoirFaire);

        if (empty($objectifs)) {
            // Pas d'objectifs, garder l'état actuel
            Log::info('Savoir-faire sans objectifs, état conservé', [
                'uuid' => $savoirFaire['uuid'] ?? 'inconnu',
                'type' => $this->getSavoirfaireTypeCode($savoirFaire)
            ]);
            return;
        }

        // ✅ Vérifier si tous les objectifs sont validés
        $tousValides = true;
        $datePlusRecente = null;
        $valideurPlusRecent = null;

        foreach ($objectifs as $objectif) {
            $etatValid = $objectif['etat_valid'] ?? false;
            
            if (!$etatValid) {
                $tousValides = false;
                break;
            }

            // Récupérer la date la plus récente
            if (isset($objectif['date_validation'])) {
                $dateObjectif = Carbon::parse($objectif['date_validation']);
                if ($datePlusRecente === null || $dateObjectif->gt($datePlusRecente)) {
                    $datePlusRecente = $dateObjectif;
                    $valideurPlusRecent = $objectif['valideur'] ?? 'Auto-calculé';
                }
            }
        }

        // ✅ Mettre à jour le savoir-faire
        $savoirFaire['etat_valid'] = $tousValides;

        if ($tousValides && $datePlusRecente) {
            $savoirFaire['date_validation'] = $datePlusRecente->toDateTimeString();
            $savoirFaire['valideur'] = $valideurPlusRecent;
        }

        Log::info('Savoir-faire recalculé', [
            'uuid' => $savoirFaire['uuid'] ?? 'inconnu',
            'type' => $this->getSavoirfaireTypeCode($savoirFaire),
            'etat_valid' => $savoirFaire['etat_valid'],
            'nb_objectifs' => count($objectifs),
            'tous_valides' => $tousValides
        ]);
    }

    /**
     * ✅ Récupère le code du type de savoir-faire
     */
    private function getSavoirfaireTypeCode(array $savoirfaire): ?string
    {
        return $savoirfaire['savoirfaire_type']['code'] ?? 
               $savoirfaire['savoirfaireType']['code'] ?? 
               $savoirfaire['type_code'] ?? 
               'inconnu';
    }

    /**
     * ✅ Récupère les objectifs pour le recalcul
     */
    private function getObjectifsPourRecalcul(array $savoirFaire): array
    {
        // ✅ Objectifs directement dans le savoir-faire
        if (isset($savoirFaire['objectifs']) && is_array($savoirFaire['objectifs'])) {
            return $savoirFaire['objectifs'];
        }

        // ✅ Objectifs via pivot ID
        $pivotId = $savoirFaire['pivot']['id'] ?? null;
        if ($pivotId) {
            try {
                $anneeSavoirfaire = \Modules\FcmCentral\Models\AnneeSavoirfaire::with('objectifs')
                    ->find($pivotId);
                
                if ($anneeSavoirfaire && $anneeSavoirfaire->objectifs) {
                    return $anneeSavoirfaire->objectifs->map(function ($objectif) {
                        return [
                            'id' => $objectif->id,
                            'uuid' => $objectif->id,
                            'etat_valid' => false, // ✅ À adapter selon ta logique de suivi des validations
                            'date_validation' => null,
                            'valideur' => null,
                        ];
                    })->toArray();
                }
            } catch (\Exception $e) {
                Log::warning("Erreur récupération objectifs pour recalcul pivot {$pivotId}: " . $e->getMessage());
            }
        }

        return [];
    }

    /**
     * ✅ VALIDATION ENFANTS-PARENTS : Si tous les enfants sont cochés → parent devient valide
     */
    protected function validerSiTousEnfantsSontCoches(array &$parent, string $typeEnfants): void
    {
        $enfants = $parent[$typeEnfants] ?? [];
        
        // Si pas d'enfants, garder l'état actuel
        if (empty($enfants)) {
            return;
        }

        // VÉRIFICATION SI TOUS LES ENFANTS SONT COCHÉS
        $tousCoches = true;
        $datePlusRecente = null;
        $valideurPlusRecent = null;

        foreach ($enfants as $enfant) {
            // Si un enfant n'est pas coché → parent non valide
            if (!($enfant['etat_valid'] ?? false)) {
                $tousCoches = false;
                break;
            }

            // Récupérer la date la plus récente
            if (isset($enfant['date_validation'])) {
                $dateEnfant = Carbon::parse($enfant['date_validation']);
                if ($datePlusRecente === null || $dateEnfant->gt($datePlusRecente)) {
                    $datePlusRecente = $dateEnfant;
                    $valideurPlusRecent = $enfant['valideur'] ?? 'Auto-calculé';
                }
            }
        }

        // MAJ PARENT
        $parent['etat_valid'] = $tousCoches;

        // Si tous cochés → ajouter les métadonnées de validation
        if ($tousCoches && $datePlusRecente) {
            $parent['date_validation'] = $datePlusRecente->toDateTimeString();
            $parent['valideur'] = $valideurPlusRecent;
        }

        // LOG
        Log::info('Élément recalculé', [
            'type' => $typeEnfants,
            'uuid' => $parent['uuid'] ?? 'inconnu',
            'etat_valid' => $parent['etat_valid'],
            'nb_enfants' => count($enfants),
            'tous_coches' => $tousCoches
        ]);
    }
}
