<?php

namespace Modules\FcmCommun\Services;

use Carbon\Carbon;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use Modules\FcmCommun\DataObjects\ParcoursDto;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\UserParcours;
use Modules\FcmCentral\Services\ParcoursService;

use Modules\FcmCentral\Events\UserGeneratedEvent;

class LivretService
{
    public static $UserGeneratedEvent = UserGeneratedEvent::class;
    public static $ParcoursSerialise  = ParcoursSerialise::class;
    public static $ParcoursService    = ParcoursService::class;
    public static $UserParcours       = UserParcours::class;

    public static function get_livret($uuid)
    {
        // On charge un parcours serialise, mais ensuite, cela devra etre le parcours serialise d'un utilisateur.
        $parcours = static::$UserParcours::where("user_id", $uuid)->get();
        //dump($parcours);
        $dtos = $parcours->map(function($p) {
            return ParcoursDto::from($p->parcours);
        });

        //ddd($dtos);

        $nodes = Arr::keyBy(static::$ParcoursService::transform_for_treeview($dtos->toArray()), "id" );
        //ddd($nodes);

        $roots = static::$ParcoursService::get_roots_for_treeview($dtos->toArray());
        //ddd($roots);

        $livret = [
                "roots" => $roots,
                "nodes" => $nodes
        ];

        //dump($livret);
        return $livret;
        //return json_encode($livret);
    }

    public static function update_livret($uuid, $livret, $commentaire)
    {
        $parcours = static::$UserParcours::where("user_id", $uuid)->get();

        $dtos = $parcours->map(function($p) {
            return ParcoursDto::from($p->parcours);
        });

        $nodes = Arr::keyBy(static::$ParcoursService::transform_for_treeview($dtos->toArray()), "id" );

        $livret_after=Arr::dot($livret);
        //ddd($livret_after);
        $livret_after=Arr::where($livret_after, function ($value, $key) {
            if (Str::contains($key, "checked")) return true;
            return false;
        });
        //ddd($livret_after);
        //dump('-----------------------');

        $livret_before = Arr::dot($nodes);
        $livret_before=Arr::where($livret_before, function ($value, $key) {
            if (Str::contains($key, "checked")) return true;
            return false;
        });
        //ddd($livret_before);
        //dump('-----------------------');
        //ddd($secondColl);

        $changes=[];
        foreach ($livret_before as $key => $value){
            if ($livret_before[$key] != $livret_after[$key]){
                $changes[$key] = $livret_after[$key] ? "set" : "unset";
            }
        }
        //ddd($changes);
        $changes=Arr::undot($changes);
        //ddd($changes);
        
        static::$ParcoursService::applyChanges($parcours, $changes, $commentaire);

        return;
    }
    
}