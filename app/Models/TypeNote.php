<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;



class TypeNote extends Model
{


    
    protected $fillable = [
        'libelle_long',
        'libelle_court', 
    ];

    public function notes()
    {
        return $this->hasMany(Note::class);
    }


    
    }