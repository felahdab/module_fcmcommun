<?php

namespace Modules\FcmCommun\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCommun\Helpers\DataParcoursMarinHelper;
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
            
            // Je prends le nouveau parcours, et j'ajoute les champs avec des valeurs si elles existent
            // je recrée le parcours marin en ajoutant les champs supplémentaires déjà rempli s'il y a correspondance uuid entre l'existant et le nouveau.
            $newparcours=$parcours->parcours;
            $old_list_fonction=$outils->fonctions_du_parcours();
            $old_list_competence=$outils->competences_du_parcours();
            $old_list_savoirfaire=$outils->savoirfaires_du_parcours();
            $old_list_activite=$outils->activites_du_parcours();
            foreach($newparcours['fonction'] as $fonction){
                if (isset($old_list_fonction[$fonction['uuid']])){
                    $data['commentaire'] = $old_list_fonction['commentaire'];
                    $data['etat_valid'] = $old_list_fonction['etat_valid'];
                    $data['valideur'] = $old_list_fonction['valideur'];
                    $data['date_validation'] = $old_list_fonction['date_validation'];
                    $data['commentaire_proposition'] = $old_list_fonction['commentaire_proposition'];
                    $data['date_proposition'] = $old_list_fonction['date_proposition'];
                }
                else{
                    $data['commentaire']= null;
                    $data['etat_valid']= false;
                    $data['valideur']= null;
                    $data['date_validation']= null;
                    $data['commentaire_proposition']= null;
                    $data['date_proposition']= null;
                }
                $newparcours['fonction'][$fonction['uuid']]=$data;
            
            }

            // Je fais l'event
            // $event = new UserGeneratedEvent(
            //     UserGeneratedEventTypes::MAJ_PARCOURS_SERIALISE_MARIN,
            //     auth()->user()->uuid,
            //     [
                // rh_uuid, ancien parcours, nouveau parcours,
            //         "parcours" =>  $data,
            //     ]
            // );
            // event($event);    
    }

    //     rajoutChampSupplementaire(array $data, array $addFields = null, string $mode='init'): array
    // {
    //     // Mode 'init', injection des champs stantards et supplementaires
    //     if ($mode==='init'){
    //         // Champs a injecter fixe
    //         $data['commentaire']                = null;
    //         $data['etat_valid']                 = false;
    //         $data['valideur']                   = null;
    //         $data['date_validation']            = null;
    //         $data['commentaire_proposition']    = null;
    //         $data['date_proposition']           = null;

        //
        // je récupère les données du ppa à appliquer (uuid et parcours complet)
        
        // je cherche tous les marins qui ont ce parours_uuid.

        // Pour chaque marin je crée à partir ancien parcours
        //tableau activité [uuid, lib, etat, comment, valideur, date]
        //tableau sf [uuid, lib, etat, comment, valideur, date]
        //tableau comp [uuid, lib, etat, comment, valideur, date]
        //tableau fonc [uuid, lib, etat, comment, valideur, date]

        //je recrée le parcours marin en ajoutant les champs supplémentaires déjà rempli s'il y a correspondance uuid entre l'existant et le nouveau.
        
    }
}
