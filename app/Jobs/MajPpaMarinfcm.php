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
        // je vais passer par les event pour la maj des parcours de tous les marins.
        // Je fais un event MAJ_PARCOURS_SERIALISE_MARIN par marin mis a jour
        // Les parametres sont : rh_uuid, ancien parcours, nouveau parcours, uuid_user_fixateur
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
            // Je le découpe en tableaux pour comparer avec le nouveau parcours.
            $activites=[];
            $sf=[];
            $comp=[];
            $fonc=[];
            // Je prends le nouveau parcours, et j'ajoute les champs avec des valeurs si elles existent
            // je recrée le parcours marin en ajoutant les champs supplémentaires déjà rempli s'il y a correspondance uuid entre l'existant et le nouveau.

            // Je fais l'event
            // $event = new UserGeneratedEvent(
            //     UserGeneratedEventTypes::MAJ_PARCOURS_SERIALISE_MARIN,
            //     auth()->user()->uuid,
            //     [
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
