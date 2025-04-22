<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;

use Modules\FcmCommun\DataObjects\SavoirFaireDto;

class CompetenceDto extends Data
{
    public string $id;
    public string $libelle_long;
    public string $libelle_court;
    public string $url;
    public ?string $same;
    public array $state = [ "checked" => false, "opened" => true];

    /** @var Collection<int, SavoirFaireDto> */
    public Collection $savoirfaires;
    
}
