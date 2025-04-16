<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\FcmCommun\Models\Marin ;
use Modules\FcmCommun\Models\Cohorte;



class FcmMarin extends Model
{
    
   

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'rh_marin_id',
        'data',
        'cohorte_id',
        'mentor_id'
    ];

    protected $casts = [
        'data' => 'array',
    ];

  
    public function marin()
    {
        return $this->belongsTo(Marin::class,'rh_marin_id');
    }

    public function cohorte()
    {
        return $this->belongsTo(Cohorte::class);
    }


 

    public function mentor()
    {
        return $this->belongsTo(Marin::class,'mentor_id');
    }
   

    

}
