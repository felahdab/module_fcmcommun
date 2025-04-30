<?php

namespace Modules\FcmCommun\Traits;

use Exception;


trait LoadsModelsDynamically
{
   /**
    * Charge dynamiquement un modele en fonction du préfixe
    * @param string $prefix
    * @param string @modelName
    * @return string
    * @throws Exception
    */

    public function getModelClass(?string $prefix, ?string $modelName):string
    {
        // Définir les namespaces
        $namespaces = [
            'fcmcentral_'   => 'Modules\\FcmCentral\\Models\\',
            'fcmunite_'     => 'Modules\\FcmUnite\\Models\\',
        ];

        if(!isset($namespaces[$prefix])){
            throw new Exception("Préfixe non reconnu : {$prefix}");
        }

        // Construire le chemin pour le model
        $modelClass = $namespaces[$prefix]. $modelName;

        if(!class_exists($modelClass)){
            throw new Exception("Le modele {$modelClass} n'existe pas");
        }

        return $modelClass;
    }


   

}