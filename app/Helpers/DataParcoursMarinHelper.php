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
        foreach(['annees','savoirfaires','objectifs'] as $relation){
            if (isset($data[$relation]) && is_array($data[$relation])){
                foreach ($data[$relation] as $index => $child) {
                    $data[$relation][$index] = self::rajoutChampSupplementaire($child);
                }
            }
        }
       return $data; 

    }

    // Cette fonction va permettre de mettre à jour les champs supplémentaires du parcours d'un marin en comparaison de la version précédente.
        // $parcours : le parcours de parcours serialisé (pas le parcours avec les champs déjà ajoutés comme dans parcours marin)
        // $tableauxValeurs : ensemble des tableaux de comparaison pour saisir les données s'il y en a
        //  exemple : $tableauxValeurs = [
        //     'niveau1' => $outils->fonctions_valid_du_parcours(), // fonction
        //     'niveau2' => $outils->competences_valid_du_parcours(), // competence
        //     'niveau3' => $outils->savoirfaires_valid_du_parcours(), // savoirfaire
        //     'niveau4' => $outils->activites_valid_du_parcours(), // activite];
        // $niveau : permet de selectionner dans tableauxValeurs la ligne à consulter
    public static function rajoutChampSupplementaireAvecValeurs(array $parcours, array $tableauxValeurs, $niveau=1): array {
        $tablcompar = $tableauxValeurs['niveau' . $niveau] ?? [];
        foreach ($parcours as &$element) {
            // Ajouter les champs avec la valeur nulle par défaut
            $element['commentaire']                = null;
            $element['etat_valid']                 = false;
            $element['valideur']                   = null;
            $element['date_validation']            = null;
            $element['commentaire_proposition']    = null;
            $element['date_proposition']           = null;
            if (isset($tablcompar[$element['uuid']])) {
                $valeurs = $tablcompar[$element['uuid']];
                $element['commentaire']                = $valeurs['commentaire'];
                $element['etat_valid']                 = $valeurs['etat_valid'];
                $element['valideur']                   = $valeurs['valideur'];
                $element['date_validation']            = $valeurs['date_validation'];
                $element['commentaire_proposition']    = $valeurs['commentaire_proposition'];
                $element['date_proposition']           = $valeurs['date_proposition'];
            }
            if (is_array($element)) {
                self::rajoutChampSupplementaireAvecValeurs($element, $tableauxValeurs, $niveau + 1);
            }
        }
        return $parcours;
    }


}
