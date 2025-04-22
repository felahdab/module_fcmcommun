<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;

class ActiviteDto extends Data
{
    public string $id;
    public string $libelle_court;  
    public string $libelle_long;
    public ?string $url;
    public ?string $duree_validite;
    public ?string $prerequis;
    public string $type;
    public array $state = [ "checked" => false, "opened" => true];
    public ?string $date_valid;
    public ?string $valideur;

}
