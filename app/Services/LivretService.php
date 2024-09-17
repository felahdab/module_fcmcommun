<?php

namespace Modules\FcmCommun\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;


class LivretService
{

    public function __construct(public $parcours_service, public string $marinparcours)
    {

    }

    public function get_livret($uuid)
    {
        $dtos = $this->parcours_service->get_parcours_for_marin_as_dtos($uuid);
        $dtos_as_array=$dtos->toArray();

        $nodes = Arr::keyBy($this->parcours_service->transform_for_treeview($dtos_as_array), "id" );

        $roots = $this->parcours_service->get_roots_for_treeview($dtos->toArray());

        $livret = [
                "roots" => $roots,
                "nodes" => $nodes
        ];

        return $livret;
    }

    public function update_livret($uuid, $livret, $commentaire)
    {
        $parcours = $this->marinparcours::where("marin_id", $uuid)->get();

        $dtos = $this->parcours_service->get_parcours_for_marin_as_dtos($uuid);
        $dtos_as_array=$dtos->toArray();

        $nodes = Arr::keyBy($this->parcours_service->transform_for_treeview($dtos_as_array), "id" );

        $livret_before = Arr::dot($nodes);
        $livret_before=Arr::where($livret_before, function ($value, $key) {
            if (Str::contains($key, "checked")) return true;
            return false;
        });
        
        $livret_after=Arr::dot($livret);
        $livret_after=Arr::where($livret_after, function ($value, $key) {
            if (Str::contains($key, "checked")) return true;
            return false;
        });

        $changes=[];
        foreach ($livret_before as $key => $value){
            if ($livret_before[$key] != $livret_after[$key]){
                $changes[$key] = $livret_after[$key] ? "set" : "unset";
            }
        }

        $changes=Arr::undot($changes);
        
        $this->parcours_service->applyChanges($parcours, $changes, $commentaire);

        return;
    }
    
}