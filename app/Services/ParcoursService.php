<?php

namespace Modules\FcmCommun\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

use Modules\FcmCommun\DataObjects\ParcoursDto;
use Modules\FcmCommun\Models\Marin;

class ParcoursService
{
    public string $marin_parcours_class;
    public string $user_generated_event_class;
    

    private function adjust_node_state($node)
    {
        // Si le noeud n'a pas de state on donne un state par defaut
        if (! array_key_exists("state", $node)){
            $node["state"] = [
                "checked" => false,
                "opened" => false
            ];
        }

        // Si le noeud a un state mais pas de checked dans celui-ci, on
        // definit la valeur par defaut de checked.
        if (! array_key_exists("checked", $node["state"])){
            $node["state"]["checked"] =  false;
        }

        return $node;
    }

    private function generate_node_from_array_by_key(&$arr, $key)
    {
        $ret = [];
        $content = Arr::pull($arr, $key);
        $arr["children"] = Arr::pluck($content, "id");
        $arr = $this->adjust_node_state($arr);

        $content = $this->transform_for_treeview($content);

        $ret = array_merge([$arr], $content);

        return $ret;
    }

    public function transform_for_treeview(&$dtos_as_array)
    {
        $ret = [];

        foreach($dtos_as_array as $arr){
            $arr["text"] = $arr["libelle_long"];

            if (Arr::exists($arr, "annees")){
                $ret = array_merge($ret, $this->generate_node_from_array_by_key($arr, 'annees'));
            }
            // elseif (Arr::exists($arr, "competences")){
            //     $ret = $this->generate_node_from_array_by_key($arr, 'competences');
            // }
            elseif (Arr::exists($arr, "savoirfaires")){
                $ret = $this->generate_node_from_array_by_key($arr, 'savoirfaires');
            }
            elseif (Arr::exists($arr, "objectifs")){
                $ret = $this->generate_node_from_array_by_key($arr, 'objectifs');
            }
            else {
                $arr = $this->adjust_node_state($arr);
                $ret[] = $arr;
            }
        }        

        return $ret;
    }

    public function get_roots_for_treeview($arrs)
    {
        return Arr::pluck($arrs, "id");
    }

    public function get_parcours_for_marin_as_dtos($uuid)
    {
        $parcours_du_marin = $this->marin_parcours_class::where("marin_id", $uuid)->get();

        $dtos = $parcours_du_marin->map(function($p) {
            return ParcoursDto::from($p->parcours);
        });

        return $dtos;
    }

    public function applyChanges(Collection $tous_marinparcours_du_marin, $changes, $commentaire)
{
    foreach($tous_marinparcours_du_marin as $marinparcours){
        $dto = ParcoursDto::from($marinparcours->parcours);
       
        // NOUVELLE STRUCTURE
        foreach($dto->annees as &$annee){
           
            // Validation des années
            if (Arr::exists($changes, $annee['id'])){
                $annee['state'] = $changes[$annee['id']]["state"];
               
                $event = new $this->user_generated_event_class(
                    event_type: $changes[$annee['id']]["state"]["checked"] == "set" ? "valide_annee" : "invalide_annee",
                    user_id: auth()->user()->uuid,
                    object_class: "Marin",
                    object_uuid: Marin::findOrFail($marinparcours->marin_id)->nid,
                    detail: [
                        "marinparcours_id" => $marinparcours->id,
                        "parcours_id" => $marinparcours->parcours_id,
                        "annee_id" => $annee['id'],
                        "commentaire" => $commentaire
                    ]
                );

                event($event);
            }

            foreach($annee['savoirfaires'] as &$savoirfaire){
               
                if (Arr::exists($changes, $savoirfaire['id'])){
                    $savoirfaire['state'] = $changes[$savoirfaire['id']]["state"];
                   
                    $event = new $this->user_generated_event_class(
                        event_type: $changes[$savoirfaire['id']]["state"]["checked"] == "set" ? "valide_savoirfaire" : "invalide_savoirfaire",
                        user_id: auth()->user()->uuid,
                        object_class: "Marin",
                        object_uuid: Marin::findOrFail($marinparcours->marin_id)->nid,
                        detail: [
                            "marinparcours_id" => $marinparcours->id,
                            "parcours_id" => $marinparcours->parcours_id,
                            "savoirfaire_id" => $savoirfaire['id'],
                            "commentaire" => $commentaire
                        ]
                    );

                    event($event);
                }

                foreach($savoirfaire['objectifs'] as &$objectif){
                   
                    if (Arr::exists($changes, $objectif['id'])){
                        $objectif['state'] = $changes[$objectif['id']]["state"];
                       
                        $event = new $this->user_generated_event_class(
                            event_type: $changes[$objectif['id']]["state"]["checked"] == "set" ? "valide_objectif" : "invalide_objectif",
                            user_id: auth()->user()->uuid,
                            object_class: "Marin",
                            object_uuid: Marin::findOrFail($marinparcours->marin_id)->nid,
                            detail: [
                                "marinparcours_id" => $marinparcours->id,
                                "parcours_id" => $marinparcours->parcours_id,
                                "objectif_id" => $objectif['id'],
                                "commentaire" => $commentaire
                            ]
                        );

                        event($event);
                    }
                }
            }
        }

        $marinparcours->parcours = $dto->toArray();
        $marinparcours->save();
    }
}


    public function attribuer_parcours_a_un_user($marin, $parcours)
    {
        $event = new $this->user_generated_event_class(
            event_type: "attribue_parcours",
            user_id: auth()->user()->uuid,
            object_class: "Marin",
            object_uuid: Marin::findOrFail($marin->id)->nid,
            detail: [
                "parcoursserialise" => $parcours->id,
                // "parcours" => $parcours->uuid,
                // "version" => $parcours->version,
            ]
        );

        event($event);
    }

    public function retirer_parcours_a_un_user($marin, $parcours)
    {
        $event = new $this->user_generated_event_class(
            event_type: "retire_parcours",
            user_id: auth()->user()->uuid,
            object_class: "Marin",
            object_uuid: Marin::findOrFail($marin->id)->nid,
            detail: [
                "parcoursserialise" => $parcours->id,
                // "parcours" => $parcours->uuid,
                // "version" => $parcours->version,
            ]
        );

        event($event);
    }
}
