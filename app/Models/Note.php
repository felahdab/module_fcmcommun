<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;




class Note extends Model
{

    
    protected $fillable = [
        'uuid',
        'libelle',
        'rh_marin_id',
        'type_note_id',
        'date',
        'redacteur',
        'valide_bcf',
        'lienurl',
        'objet_type',
        'objet_id'
    ];

    protected $casts = [
        'date' => 'date',
        'valide_bcf' => 'boolean',
        'objet_id' => 'integer'
    ];



    

    
    }