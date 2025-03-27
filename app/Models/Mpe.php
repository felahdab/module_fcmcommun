<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Modules\FcmCommun\Traits\HasTablePrefix;
use Modules\RH\Models\Unite;

class Mpe extends Model
{
    use HasTablePrefix;
    use HasUuids;

    /*
      The attributes that are mass assignable.
    


    protected $fillable = [];
    public function __construct(array $attributes = [])
    {
        parent::_construct($attributes);
        // Init de fillable dans le constructeur
        $this->fillable = [
            $this->prefix . 'marin_id',
            'rh_unite_id',
            'sansdate',
            'date_debut',
            'date_fin',
        ];
    }
     */


    protected $fillable = [
        'marin_id',
        'rh_unite_id',
        'sansdate',
        'date_debut',
        'date_fin',
    ];

    public function unite()
    {
        return $this->belongsTo(Unite::class, 'rh_unite_id');
    }
}
