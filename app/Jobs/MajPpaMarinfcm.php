<?php

namespace Modules\FcmCommun\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCommun\Helpers\DataParcoursMarinHelper; // a l'air de ne faire que les activites et pas les autres elements
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCentral\Models\Parcours;

use Illuminate\Support\Facades\Log;

use Modules\FcmCommun\Services\OutilsMarinParcoursService;

#TODO: regarder s'il ne vaut pas mieux le passer dans FcmCentral. Même problématique que pour le livretFCM.

class MajPpaMarinfcm implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    
    public $parcoursserialise_uuid;
    
    /**
     * Create a new job instance.
     */
    public function __construct($parcoursserialise_uuid)
    {
        $this->parcoursserialise_uuid = $parcoursserialise_uuid;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
{
    Log::info(' JOB DEMARRE - MajPpaMarinfcm' , [
        'timestamp' => now(),
        'uuid' => $this->parcoursserialise_uuid,
    ]);
    // 1. Récupérer le nouveau parcours serialisé
    $nouveauParcours = ParcoursSerialise::where('uuid', $this->parcoursserialise_uuid)->firstOrFail();

    // 2. Récupérer l'ancienne version (celle dont la date_fin = date_debut du nouveau)
    $ancienParcours = ParcoursSerialise::where('parcours_id', $nouveauParcours->parcours_id)
        ->where('date_fin', '=', $nouveauParcours->date_debut)
        ->first();

    if (!$ancienParcours) return;

    // 3. Récupérer tous les MarinParcours liés à l'ancienne version
    $marinParcoursList = MarinParcours::where('parcoursserialise_id', $ancienParcours->id)->get();

    foreach ($marinParcoursList as $marinParcours) {
        // 4. Récupérer l'ancien parcoursmarin
        $ancienParcoursMarin = $marinParcours->parcoursmarin;
        //Log::info("Ancien Parcours ".json_encode($ancienParcoursMarin, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)." ");

        // 5. Extraire les états avec le service
        $outils = new OutilsMarinParcoursService($ancienParcoursMarin);

        $tableauxValeurs = [
            'niveau1' => $outils->affectations_valid_du_parcours($ancienParcoursMarin),
            'niveau2' => $outils->competences_valid_du_parcours($ancienParcoursMarin),
            'niveau3' => $outils->savoirfaires_valid_du_parcours($ancienParcoursMarin),
            'niveau4' => $outils->objectifs_valid_du_parcours($ancienParcoursMarin),
        ];

        // 6. Appliquer ces états sur la nouvelle structure
        $nouveauParcoursData = $nouveauParcours->parcours;
        $nouveauParcoursMarin = DataParcoursMarinHelper::rajoutChampSupplementaireAvecValeurs($nouveauParcoursData, $tableauxValeurs, 1);
        //Log::info("Nouveau Parcours ".json_encode($nouveauParcoursMarin, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)." ");
        //dd($nouveauParcoursMarin);
        // 7. Mettre à jour le parcoursmarin du MarinParcours
        //$marinParcours->parcoursmarin = json_encode($nouveauParcoursMarin, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $marinParcours->parcoursmarin = $nouveauParcoursMarin;
        $marinParcours->parcoursserialise_id = $nouveauParcours->id;
        $marinParcours->save();
    }
}

   

}
