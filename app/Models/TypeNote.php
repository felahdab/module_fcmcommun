<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Modules\FcmCommun\Traits\HasTablePrefix;


class TypeNote extends Model
{
    use HasTablePrefix;
    use HasUuids;
    
    protected $fillable = [
        'libelle_long',
        'libelle_court', 
    ];

    public function notes()
    {
        return $this->hasMany(Note::class);
    }


    
    }