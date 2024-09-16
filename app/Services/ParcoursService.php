<?php

namespace Modules\FcmCommun\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

use Modules\FcmCommun\DataObjects\ParcoursDto;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCentral\Events\UserGeneratedEvent;


class ParcoursService
{

    public static $UserGeneratedEvent = UserGeneratedEvent::class;
    public static $ParcoursSerialise = ParcoursSerialise::class;
    public static $MarinParcours       = MarinParcours::class;

    private static function adjust_node_state($node)
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

    private static function generate_node_from_array_by_key(&$arr, $key)
    {
        $ret = [];
        $content = Arr::pull($arr, $key);
        $arr["children"] = Arr::pluck($content, "id");
        $arr = static::adjust_node_state($arr);

        $content = static::transform_for_treeview($content);

        $ret = array_merge([$arr], $content);

        return $ret;
    }

    public static function transform_for_treeview(&$dtos_as_array)
    {
        $ret = [];

        foreach($dtos_as_array as $arr){
            $arr["text"] = $arr["libelle_long"];

            if (Arr::exists($arr, "fonctions")){
                $ret = array_merge($ret, static::generate_node_from_array_by_key($arr, 'fonctions'));
            }
            elseif (Arr::exists($arr, "competences")){
                $ret = static::generate_node_from_array_by_key($arr, 'competences');
            }
            elseif (Arr::exists($arr, "savoirfaires")){
                $ret = static::generate_node_from_array_by_key($arr, 'savoirfaires');
            }
            elseif (Arr::exists($arr, "activites")){
                $ret = static::generate_node_from_array_by_key($arr, 'activites');
            }
            else {
                $arr = static::adjust_node_state($arr);
                $ret[] = $arr;
            }
        }        

        return $ret;
    }

    public static function get_roots_for_treeview($arrs)
    {
        return Arr::pluck($arrs, "id");
    }

    public static function get_parcours_for_marin_as_dtos($uuid)
    {
        $parcours_du_marin = static::$MarinParcours::where("marin_id", $uuid)->get();

        $dtos = $parcours_du_marin->map(function($p) {
            return ParcoursDto::from($p->parcours);
        });

        return $dtos;
    }

    public static function applyChanges(Collection $tous_marinparcours_du_marin, $changes, $commentaire)
    {
        foreach($tous_marinparcours_du_marin as $marinparcours){
            $dto = ParcoursDto::from($marinparcours->parcours);
            
            foreach($dto->fonctions as $fonction){
                foreach($fonction->competences as $competence){
                    foreach ($competence->savoirfaires as $savoirfaire){
                        foreach($savoirfaire->activites as $activite){
                            if (Arr::exists($changes, $activite->id)){
                                $activite->state = $changes[$activite->id]["state"]["checked"] == "set" ? ["checked"=> true] : ["checked" => false ] ;
                                // Raise an event for the modification of the state of the parcours.

                                $event = new static::$UserGeneratedEvent(
                                    event_type: $changes[$activite->id]["state"]["checked"] == "set" ? "valide_activite" : "invalide_activite",
                                    user_id: auth()->user()->uuid,
                                    object_class: "Marin", 
                                    object_uuid: $marinparcours->marin_id,
                                    detail: [
                                        "marinparcours_id" => $marinparcours->id,
                                        "parcours_id" => $marinparcours->parcours_id,
                                        "activite_id" => $activite->id,
                                        "commentaire" => $commentaire
                                    ]
                                );

                                event($event);
                            }
                        }

                        if (Arr::exists($changes, $savoirfaire->id)){
                            $savoirfaire->state = $changes[$savoirfaire->id]["state"]["checked"] == "set" ? ["checked"=> true] : ["checked" => false ] ;
                            // Raise an event for the modification of the state of the parcours.

                            $event = new static::$UserGeneratedEvent(
                                event_type: $changes[$savoirfaire->id]["state"]["checked"] == "set" ? "valide_savoirfaire" : "invalide_savoirfaire",
                                user_id: auth()->user()->uuid,
                                object_class: "Marin", 
                                object_uuid: $marinparcours->marin_id,
                                detail: [
                                    "marinparcours_id" => $marinparcours->id,
                                    "parcours_id" => $marinparcours->parcours_id,
                                    "savoirfaire_id" => $savoirfaire->id,
                                    "commentaire" => $commentaire
                                ]
                            );

                            event($event);
                        }

                    }
                }
            }
            $marinparcours->parcours=$dto;
            $marinparcours->save();
        }
    }

    public static function attribuer_parcours_a_un_user($marin, $parcours)
    {
        $event = new static::$UserGeneratedEvent(
            event_type: "attribue_parcours",
            user_id: auth()->user()->uuid,
            object_class: get_class($marin),
            object_uuid: $marin->id,
            detail: [
                "parcoursserialise" => $parcours->id,
                // "parcours" => $parcours->uuid,
                // "version" => $parcours->version,
            ]
        );

        event($event);
    }

    public static function retirer_parcours_a_un_user($marin, $parcours)
    {
        $event = new static::$UserGeneratedEvent(
            event_type: "retire_parcours",
            user_id: auth()->user()->uuid,
            object_class: get_class($marin),
            object_uuid: $marin->id,
            detail: [
                "parcoursserialise" => $parcours->id,
                // "parcours" => $parcours->uuid,
                // "version" => $parcours->version,
            ]
        );

        event($event);
    }
}