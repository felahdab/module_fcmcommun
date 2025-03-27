<?php

namespace Modules\FcmCommun\Models;
use Illuminate\Database\Eloquent\Model;



class Marin extends Model
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

 

   
   

    

}
