<?php
 
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
     * Recalcule tous les états en remontant la hiérarchie
     */
    public function recalculerTousLesEtats(array &$parcoursMarin): void
    {
        foreach ($parcoursMarin['fonctions'] as &$fonction) {
            if (isset($fonction['competences'])) {
                foreach ($fonction['competences'] as &$competence) {
                    if (isset($competence['savoirfaires'])) {
                        foreach ($competence['savoirfaires'] as &$savoirFaire) {
                            //  1. RECALCULER LE SAVOIR-FAIRE
                            $this->validerSiTousEnfantsSontCoches($savoirFaire, 'activites');
                        }
                        //  2. RECALCULER LA COMPÉTENCE
                        $this->validerSiTousEnfantsSontCoches($competence, 'savoirfaires');
                    }
                }
                //  3. RECALCULER LA FONCTION
                $this->validerSiTousEnfantsSontCoches($fonction, 'competences');
            }
        }
    }
 
    /**
     * VALIDATION ENFANTS -PARENTS : Si tous les enfants sont cochés → parent devient valide
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
 
        //  MAJ PARENT
        $parent['etat_valid'] = $tousCoches;
 
        // Si tous cochés → ajouter les métadonnées de validation
        if ($tousCoches && $datePlusRecente) {
            $parent['date_validation'] = $datePlusRecente->toDateTimeString();
            $parent['valideur'] = $valideurPlusRecent;
        }
 
        //  LOG
        Log::info('Élément recalculé', [
            'type' => $typeEnfants,
            'uuid' => $parent['uuid'] ?? 'inconnu',
            'etat_valid' => $parent['etat_valid'],
            'nb_enfants' => count($enfants),
            'tous_coches' => $tousCoches
        ]);
    }
}
