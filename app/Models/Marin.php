<?php

namespace Modules\FcmCommun\Models;


use Modules\RH\Models\Marin as RhMarin;
use Modules\FcmCommun\Models\Cohorte;

use Modules\FcmCentral\Models\FcmMarin as FcmCentralMarin;
use Modules\FcmUnite\Models\FcmMarin as FcmUniteMarin;





/**
 * Model etendue a RhMarin pour eviter d'inclure les parties FCM dans le model RH Marin
 */

class Marin extends RhMarin
{

   


     /**
     * Relations
     */
    
     public function fcmCentralMarin()
     {
         return $this->hasMany(FcmCentralMarin::class,'rh_marin_id','id');
     }
 
     public function fcmUniteMarin()
     {
         return $this->hasMany(FcmUniteMarin::class,'rh_marin_id','id');
     }
 
     public function fcmCentralMentors()
     {
         return $this->hasMany(FcmCentralMarin::class,'mentor_id','id');
     }
 
     public function fcmUniteMentors()
     {
         return $this->hasMany(FcmUniteMarin::class,'mentor_id','id');
     }
 
     public function fcmCentralCohorte()
     {
         return $this->hasOneThrough(Cohorte::class, FcmCentralMarin::class, 'rh_marin_id','id', 'id', 'cohorte_id');
     }
 
 
    /**
      * Relation pour acceder directement au mentor via FcmCentralMarin
      */
 
      public function directMentor()
      {
         return $this->hasOneThrough(
             self::class, // Modele Fini
             FcmCentralMarin::class, // Modele intermediaire
             'rh_marin_id', // Cle etrangere de fcmcentralmarins poitnant vers rh_marins
             'id', // cle etranger de rh_marins vers mentor
             'id', // cle local rh_marins
             'mentor_id' // cle locale fcmcentral_marins
         );
     }


}