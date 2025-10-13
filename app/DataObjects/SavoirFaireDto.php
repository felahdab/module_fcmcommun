<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;

use Modules\FcmCommun\DataObjects\ObjectifDto;

class SavoirfaireDto extends Data
{
    public int $id;
    public string $savoirfaire_id;
    public string $code;
    public string $libelle_long;
    public string $libelle_court;
    public ?string $competence_code;
    public int $ordre;
    public ?int $heures;
    public ?int $jours;
    public ?string $intitule;
    public ?string $savoirfaire_type;
    public ?string $color;
    public array $state = ["checked" => false, "opened" => false];

     /** @var Collection<int, ObjectifDto> */
     public Collection $objectifs;
}

