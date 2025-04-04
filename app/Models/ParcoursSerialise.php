<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;
use  Illuminate\Support\Str;
use Modules\FcmCentral\Traits\HasTablePrefix;

class ParcoursSerialise extends Model
{
    use HasTablePrefix;
 

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [ "id", 
                            "uuid",
                            "libelle_long",
                            "libelle_court", 
                            "version", 
                            "date_debut", 
                            "date_fin", 
                            "parcours",
                            "parcours_id",
                        ];

    protected $casts = [
        'parcours' => 'array',
    ];

    protected static function booted ()
    {
        static::creating(function ($model){
            $model->uuid = (string) Str::uuid();
        });
    }
    
}
