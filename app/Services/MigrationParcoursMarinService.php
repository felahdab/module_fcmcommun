<?php

namespace Modules\FcmCommun\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCommun\Helpers\DataParcoursMarinHelper;

class MigrationParcoursMarinService
{
    /**
     * Migre les MarinParcours d'anciennes versions vers une nouvelle version
     * 
     * @param string $nouveauParcoursSerialiseUuid UUID du nouveau ParcoursSerialise
     * @return array Statistiques de la migration
     */
    public function migrerVersNouvelleVersion(string $nouveauParcoursSerialiseUuid): array
    {
        Log::info('🔄 Début migration ParcoursMarins', [
            'timestamp' => now(),
            'uuid' => $nouveauParcoursSerialiseUuid,
        ]);

        $stats = [
            'total' => 0,
            'success' => 0,
            'errors' => 0,
        ];

        // 1. Récupérer le nouveau parcours serialisé
        $nouveauParcours = ParcoursSerialise::where('uuid', $nouveauParcoursSerialiseUuid)->firstOrFail();

        // 2. Récupérer toutes les anciennes versions
        $anciennesVersions = ParcoursSerialise::where('parcours_id', $nouveauParcours->parcours_id)
            ->where('id', '!=', $nouveauParcours->id)
            ->get();

        if ($anciennesVersions->isEmpty()) {
            Log::info('ℹ️ Aucune version antérieure trouvée');
            return $stats;
        }

        // 3. Récupérer tous les MarinParcours des anciennes versions
        $idsAnciennesVersions = $anciennesVersions->pluck('id')->toArray();
        
        $marinParcoursList = MarinParcours::whereIn('parcoursserialise_id', $idsAnciennesVersions)
            ->with(['fcmMarin.marin'])
            ->get();

        $stats['total'] = $marinParcoursList->count();

        Log::info('📊 MarinParcours à migrer', [
            'parcours_id' => $nouveauParcours->parcours_id,
            'nb_anciennes_versions' => $anciennesVersions->count(),
            'nb_marins' => $stats['total'],
        ]);

        // 4. Migrer chaque MarinParcours
        foreach ($marinParcoursList as $marinParcours) {
            try {
                $this->migrerMarinParcours($marinParcours, $nouveauParcours);
                $stats['success']++;
            } catch (Exception $e) {
                $stats['errors']++;
                Log::error('❌ Erreur migration MarinParcours', [
                    'marin_parcours_id' => $marinParcours->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('✅ Migration terminée', $stats);

        return $stats;
    }

    /**
     * Migre un seul MarinParcours vers la nouvelle version
     */
    private function migrerMarinParcours(MarinParcours $marinParcours, ParcoursSerialise $nouveauParcours): void
    {
        // Récupérer l'ancien parcoursmarin
        $ancienParcoursMarin = $marinParcours->parcoursmarin;

        // Extraire les états avec le service
        $outils = new OutilsMarinParcoursService($ancienParcoursMarin);

        $tableauxValeurs = [
            'niveau1' => $outils->affectations_validees_ou_proposees_du_parcours($ancienParcoursMarin),
            'niveau2' => $outils->competences_validees_ou_proposees_du_parcours($ancienParcoursMarin),
            'niveau3' => $outils->savoirfaires_valides_ou_proposes_du_parcours($ancienParcoursMarin),
            'niveau4' => $outils->objectifs_valides_ou_proposes_du_parcours($ancienParcoursMarin),
        ];

        // Appliquer ces états sur la nouvelle structure
        $nouveauParcoursData = $nouveauParcours->parcours;
        
        $nouveauParcoursMarin = DataParcoursMarinHelper::rajoutChampSupplementaireAvecValeurs(
            $nouveauParcoursData, 
            $tableauxValeurs, 
            1
        );

        // Mettre à jour le MarinParcours
        $marinParcours->parcoursmarin = $nouveauParcoursMarin;
        $marinParcours->parcoursserialise_id = $nouveauParcours->id;
        $marinParcours->save();

        Log::debug('✅ MarinParcours migré', [
            'marin_parcours_id' => $marinParcours->id,
            'ancien_ps_id' => $marinParcours->getOriginal('parcoursserialise_id'),
            'nouveau_ps_id' => $nouveauParcours->id,
        ]);
    }
}
