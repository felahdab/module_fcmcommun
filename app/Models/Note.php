<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;



class Note extends Model
{

    
    protected $fillable = [
        'libelle',
        'marin_id', 
        'date',
        'typenote_id',
        'redacteur',
        'valide_bcf',
        'lienurl',
        'objet_type',
        'objet_id',
    ];

    public function marins()
    {
        return $this->hasMany(FcmMarin::class);
    }

    public function typenote()
    {
        return $this->belongsTo(TypeNote::class,'typenote_id');
    }
    
    }