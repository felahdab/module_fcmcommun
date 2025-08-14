<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;


class MarinParcours extends Model
{
    /**
     * Les attributs ci-dessous sont communs aux modèles MarinParcours des modules FcmCentral et FcmUnité
     * qui étendent cette classe.
     */

    protected $fillable = [
        'marin_id',
        'parcours_id',
        'taux_global',
        'taux_stage',
        'taux_activite',
        'parcoursmarin',
        'parcoursserialise_id',
    ];

    protected $casts = [
        'parcoursmarin' => 'array',
    ];
}
