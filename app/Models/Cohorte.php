<?php

namespace Modules\FcmCommun\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\FcmCommun\Traits\HasTablePrefix;
use Modules\FcmCentral\Models\FcmMarin as FcmCentralMarin;
use Modules\FcmUnite\Models\FcmMarin as FcmUniteMarin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\FcmCommun\Database\Factories\CohorteFactory;

# TODO: déplacer les méthodes fcmCentralMarins vers le modele Cohorte de FCMCentral
# Idem pour fcmUniteMarins vers le modele Cohorte de FcmUnite
# Vérifier dans les module qu'on utilise le bon modèle.

class Cohorte extends Model
{
    use HasTablePrefix, HasFactory;
    
    
    protected $fillable = [
        'uuid',
        'libelle_long',
        'libelle_court', 
        'date_sortie_cours',
    ];

    public function fcmCentralMarins()
    {
        return $this->hasMany(FcmCentralMarin::class, 'cohorte_id','id');
    }

    public function fcmUniteMarins()
    {
        return $this->hasMany(FcmUniteMarin::class);
    }

      /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return CohorteFactory::new();
    }

    
    }