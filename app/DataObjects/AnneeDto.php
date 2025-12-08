<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;

use Modules\FcmCommun\DataObjects\SavoirFaireDto;

class AnneeDto extends Data
{
    public string $id;
    public string $uuid;
    public string $libelle_long;
    public string $libelle_court;
    public int $ordre;
    public ?string $color;
    public array $state = ["checked" => false, "opened" => false];

     /** @var Collection<int, SavoirFaireDto> */
     public Collection $savoirfaires;
    
}

