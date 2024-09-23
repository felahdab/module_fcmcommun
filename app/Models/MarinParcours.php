<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Modules\FcmCommun\Traits\HasTablePrefix;

use Modules\FcmCommun\Models\Marin;

class MarinParcours extends Model
{
    use HasTablePrefix;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [ "marin_id",
                            "parcours_id",
                            "parcours"];

    protected $casts = [
        'parcours' => 'array',
    ];

    public function marin() 
    {
        return $this->belongsTo(Marin::class, 'marin_id');
    }
}
