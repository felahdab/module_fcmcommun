<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Modules\FcmCommun\Traits\HasTablePrefix;


class Etat extends Model
{
    use HasTablePrefix;
    use HasUuids;
    
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