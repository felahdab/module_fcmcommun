<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;

//use Modules\FcmCommun\Traits\HasTablePrefix;
use Modules\RH\Models\Marin as RhMarin;



class MarinParcours extends Model
{
    //use HasTablePrefix;
    //use HasUuids;

    /*
     The attributes that are mass assignable.
    // Function dans le cas Si on met des champs avec le prefixe 'fcmcentral_'  exemple : fcmcentral_marin_id

    protected $fillable = [];
    public function __construct(array $attributes = [])
    {
        parent::_construct($attributes);
        // Init de fillable dans le constructeur
        $this->fillable = [
            $this->prefix.'marin_id',
            $this->prefix.'parcours_id',
            'taux_global',
            'taux_stage',
            'taux_activite',
            'parcours',
            'mentor_id',
        ];
    }

    */

    protected $fillable = [
        'marin_id',
        'parcours_id',
        'taux_global',
        'taux_stage',
        'taux_activite',
        'parcoursmarin',
        'parcoursserarialises_id',
    ];

    protected $casts = [
        'parcoursmarin' => 'array',
    ];

  


   
   
}
