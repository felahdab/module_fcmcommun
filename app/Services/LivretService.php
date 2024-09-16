<?php

namespace Modules\FcmCommun\Services;

use Carbon\Carbon;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use Modules\FcmCommun\DataObjects\ParcoursDto;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCentral\Services\ParcoursService;

use Modules\FcmCentral\Events\UserGeneratedEvent;

class LivretService
{
    public static $UserGeneratedEvent = UserGeneratedEvent::class;
    public static $ParcoursSerialise  = ParcoursSerialise::class;
    public static $ParcoursService    = ParcoursService::class;
    public static $MarinParcours      = MarinParcours::class;

    public static function get_livret($uuid)
    {
        $dtos = static::$ParcoursService::get_parcours_for_marin_as_dtos($uuid);
        $dtos_as_array=$dtos->toArray();

        $nodes = Arr::keyBy(static::$ParcoursService::transform_for_treeview($dtos_as_array), "id" );

        $roots = static::$ParcoursService::get_roots_for_treeview($dtos->toArray());

        $livret = [
                "roots" => $roots,
                "nodes" => $nodes
        ];

        return $livret;
    }

    public static function update_livret($uuid, $livret, $commentaire)
    {
        $parcours = static::$MarinParcours::where("marin_id", $uuid)->get();

        $dtos = static::$ParcoursService::get_parcours_for_marin_as_dtos($uuid);
        $dtos_as_array=$dtos->toArray();

        $nodes = Arr::keyBy(static::$ParcoursService::transform_for_treeview($dtos_as_array), "id" );

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
        
        static::$ParcoursService::applyChanges($parcours, $changes, $commentaire);

        return;
    }
    
}