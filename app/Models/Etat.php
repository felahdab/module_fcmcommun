<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;


class Etat extends Model
{

    
    protected $fillable = [
        'marin_parcours_id',
        'raison', 
        'date_debut',
        'date_fin',
    ];

    public function marinParcours()
    {
        return $this->belongsTo(MarinParcours::class,'marin_parcours_id');
    }

    public function etats()
    {
        return $this->hasMany(Etat::class);
    }
    
    }