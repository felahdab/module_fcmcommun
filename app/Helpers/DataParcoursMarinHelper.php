<?php

namespace Modules\FcmCommun\Helpers;

class DataParcoursMarinHelper
{

    /**
     * But de creer un helper qui permet d'initialiser , ajouter des champs supp ou de rajouter des champs supplementaires dans un parcours existant
     * 
     * // Exemple :
     * // Mode Init : injecter champs standards + custom
     * $parcours = DataParcoursMarinHelper::rajoutChampSupplementaire($data, null, 'init');
     * ou
     * $parcours = DataParcoursMarinHelper::rajoutChampSupplementaire($data, ['priority'=>'high'], 'init');
     * ou
     * // Moda Rajout : Ajouter en plus des champs supplementaires
     * $addditionalFields =[
     *  'alert' => 1,
     *  'sedimentation' => 1
     * ];
     * $parcours = DataParcoursMarinHelper::rajoutChampSupplementaire($data, $addditionalFields, 'rajout');
     */


    /**
     *  Injection de champ supplementaire dans chaque niveau
     * @param array @data
     * @param array|null @addFields champ personnalisés à injecter
     * @return @array
     */
    public static function rajoutChampSupplementaire(array $data, array $addFields = null, string $mode='init'): array
    {
        // Mode 'init', injection des champs stantards et supplementaires
        if ($mode==='init'){
            // Champs a injecter fixe
            $data['commentaire']                = null;
            $data['etat_valid']                 = false;
            $data['valideur']                   = null;
            $data['date_validation']            = null;
            $data['commentaire_proposition']    = null;
            $data['date_proposition']           = null;

            // Champ a injecter personnalisé
            if ($addFields !== null){
                foreach ($addFields as $field => $value ){
                    $data[$field]           = $value;
                }
            }
        }
        // Si mode 'rajout' pour ajouter seulement champ supp dans existant
        elseif ($mode==='rajout' && $addFields!==null){
            // Ajout seulement des champs add sans ecraser les autres
            foreach ($addFields as $field => $value ){
                if (!array_key_exists($field,$data)){
                    $data[$field]           = $value;
                }
            }
        }


        // Boucle et traite
        foreach(['fonctions','competences','savoirfaires','activites'] as $relation){
            if (isset($data[$relation]) && is_array($data[$relation])){
                foreach ($data[$relation] as $index => $child) {
                    $data[$relation][$index] = self::rajoutChampSupplementaire($child);
                }
            }
        }
       return $data; 

    }

   


}
