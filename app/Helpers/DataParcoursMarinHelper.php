<?php

namespace Modules\FcmCommun\Helpers;

class DataParcoursMarinHelper
{
    /**
     * Injection de champs supplémentaires dans chaque niveau du parcours
     * @param array $data
     * @param array|null $addFields
     * @param string $mode
     * @return array
     */
    public static function rajoutChampSupplementaire(array $data, array $addFields = null, string $mode = 'init'): array
    {
        // Mode 'init', injection des champs standards et supplémentaires
        if ($mode === 'init') {
            // Champs à injecter fixes
            $data['commentaire'] = null;
            $data['etat_valid'] = false;
            $data['valideur'] = null;
            $data['date_validation'] = null;
            $data['commentaire_proposition'] = null;
            $data['date_proposition'] = null;

            // Champs personnalisés
            if ($addFields !== null) {
                foreach ($addFields as $field => $value) {
                    $data[$field] = $value;
                }
            }
        }
        // Mode 'rajout' : ajouter seulement les champs supplémentaires
        elseif ($mode === 'rajout' && $addFields !== null) {
            foreach ($addFields as $field => $value) {
                if (!array_key_exists($field, $data)) {
                    $data[$field] = $value;
                }
            }
        }

        // Boucle sur chaque niveau de la structure
        if (isset($data['affectations']) && is_array($data['affectations'])) {
            foreach ($data['affectations'] as $i => $affectation) {
                $data['affectations'][$i] = self::rajoutChampSupplementaire($affectation, $addFields, $mode);
            }
        }
        if (isset($data['competences']) && is_array($data['competences'])) {
            foreach ($data['competences'] as $i => $competence) {
                $data['competences'][$i] = self::rajoutChampSupplementaire($competence, $addFields, $mode);
            }
        }
        if (isset($data['competenceSavoirfaires']) && is_array($data['competenceSavoirfaires'])) {
            foreach ($data['competenceSavoirfaires'] as $i => $csf) {
                $data['competenceSavoirfaires'][$i] = self::rajoutChampSupplementaire($csf, $addFields, $mode);
            }
        }
        if (isset($data['objectifs']) && is_array($data['objectifs'])) {
            foreach ($data['objectifs'] as $i => $objectif) {
                $data['objectifs'][$i] = self::rajoutChampSupplementaire($objectif, $addFields, $mode);
            }
        }

        return $data;
    }
}
