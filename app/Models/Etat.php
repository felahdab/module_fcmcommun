<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;


class Etat extends Model
{

    
    protected $fillable = [
        'uuid',
        'marin_parcours_id',
        'raison', 
        'typeetat_id',
        'date_debut',
        'date_fin',
    ];

    
    protected $casts = [
        'date_debut' => 'date',
        'date_fin'=> 'date',
    ];
    
    }