<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\FcmCommun\Traits\HasTablePrefix;
use Modules\FcmCentral\Models\Marin as FcmCentralMarin;
use Modules\FcmUnite\Models\Marin as FcmUniteMarin;


class Cohorte extends Model
{
    use HasTablePrefix;
    
    
    protected $fillable = [
        'libelle_long',
        'libelle_court', 
        'date_sortie_cours',
    ];

    public function fcmCentralMarins()
    {
        return $this->hasMany(FcmCentralMarin::class, 'cohorte_id','id');
    }

    public function fcmUniteMarins()
    {
        return $this->hasMany(FcmUniteMarin::class);
    }
    
    }