<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Modules\FcmCommun\Traits\HasTablePrefix;


class Note extends Model
{
    use HasTablePrefix;
    use HasUuids;
    
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
        return $this->hasMany(Marin::class);
    }

    public function typenote()
    {
        return $this->belongsTo(TypeNote::class,'typenote_id');
    }
    
    }