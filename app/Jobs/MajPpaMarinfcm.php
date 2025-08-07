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

use Modules\FcmCommun\Services\OutilsMarinParcoursService;

class MajPpaMarinfcm implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Lorsque je fixe (serialise) un parcours, un job doit etre enregistré pour se lancer à la date selectionnée à 00h01.
        // 
        // Je récupère les données du ppa à appliquer
        $parcours=ParcoursSerialise::where('uuid','=', $this->parcoursserialise_uuid)->get();
        // Je cherche l'ancienne version
        $oldppa=ParcoursSerialise::where('parcours_id','=', $parcours->id)->where('date_fin' , '=', $parcours->date_debut)->get();
        // Je cherche tous les marins qui ont ce old_parours_id.
        $listfcmmarins=MarinParcours::where('parcoursserialise_id','=', $oldppa->id)->get();
        foreach($listfcmmarins as $fcmmarin){
            // Je récupère le parcours actuel du marin
            $parcoursmarin=$fcmmarin->parcoursmarin;
            //en utilisant le service outils, je récupère sous forme de tableau par type d'element [uuid =>etat_valid,valideur,commentaire,date_validation,commentaire_proposition,date_proposition]]
            $outils= New OutilsMarinParcoursService($parcoursmarin);            
            // Je prends le nouveau parcours serialisé, et j'ajoute les champs avec des valeurs si elles existent
            $newparcours=$parcours->parcours;
            // tableau des valeurs utiles pour la maj
            $tableauxValeurs = [
                'niveau1' => $outils->fonctions_valid_du_parcours(), // fonction
                'niveau2' => $outils->competences_valid_du_parcours(), // competence
                'niveau3' => $outils->savoirfaires_du_parcours(), // savoirfaire
                'niveau4' => $outils->activites_du_parcours(), // activite;
            ];
            $newparcours = DataParcoursMarinHelper::rajoutChampSupplementaireAvecValeurs($newparcours, $tableauxValeurs, 1);

            // event à créer
            // $event = new UserGeneratedEvent(
            //     UserGeneratedEventTypes::MAJ_PARCOURS_SERIALISE_MARIN,
            //     auth()->user()->uuid, // pas sur que je l'ai vu que c'est un job lancé par un cron.
            //     [
                // rh_uuid, ancien parcours $parcoursmarin, nouveau parcours $newparcours,
            //         "parcours" =>  $data,
            //     ]
            // );
            // event($event);    
        }
    }
    
}
