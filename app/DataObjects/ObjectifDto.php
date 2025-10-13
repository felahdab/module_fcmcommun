<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;

class ObjectifDto extends Data
{
    public string $id;
    public string $uuid;
    public string $libelle_long;
    public ?string $description;
    public int $ordre;
    public array $state = [ "checked" => false, "opened" => false];


   
}
