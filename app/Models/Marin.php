<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;

use Modules\RH\Models\Marin as RhMarin;

/**
 * Model etendue a RhMarin pour eviter d'inclure les parties FCM dans le model RH Marin
 */

class Marin extends RhMarin
{

     /** 
      * On définit les accesseurs, mutateurs et scope local pour l'attribut suivi_en_fcm
      * Grâce à ces méthodes, on peut faire $marin->suivi_en_fcm pour savoir si un marin est suivi en FCM
      * Et on peut faire $marin->suivi_en_fcm = true pour marquer un marin comme étant suivi en FCM.
      * 
      * Ces 3 méthodes reposent sur le même mécanisme de stockage en base de données et doivent donc être
      * modifiées de façon coordonnées le cas échéant.
      */
     public function getSuiviEnFcmAttribute()
     {
        return Arr::get($this->data, "fcm.en_fcm", false);
     }

     public function setSuiviEnFcmAttribute(bool $suivi = true)
     {
        $data = $this->data;
        Arr::set($data, "fcm.en_fcm", $suivi);
        $this->data = $data;
        $this->save();
        return $this;
     }

     /**
      * Le scope ci-dessous permet de filtrer les marins pour n'obtenir que ceux qui sont suivi en FCM:
      * Marins::enFcm()->get() par exemple.
      */
     public function scopeEnFcm(Builder $query): void
     {
        $query->whereJsonContains("data->fcm->en_fcm", true);
     }


}