<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Modules\FcmCommun\Traits\HasTablePrefix;
use Modules\RH\Models\Marin as RhMarin;

use Modules\FcmCommun\Models\Marin;

class MarinParcours extends Model
{
    use HasTablePrefix;
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
        'parcours',
        'mentor_id',
    ];

    protected $casts = [
        'parcours' => 'array',
    ];

    public function mentor()
    {
        return $this->belongsTo(RhMarin::class,'mentor_id');
    }
   
}
