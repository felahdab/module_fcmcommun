<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;
use  Illuminate\Support\Str;


class ParcoursSerialise extends Model
{
  

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


    public static function findBy(array $critere)
    {
        return self::where($critere)->first();
    }

    public static function findAllBy(array $critere)
    {
        return self::where($critere)->get();
    }

    public function scopeByVersion($query,string $version)
    {
        return $query->where('version', $version);
    }

    public function scopeByParcoursId($query,string $parcoursId)
    {
        return $query->where('parcours_id', $parcoursId);
    }
    
}
