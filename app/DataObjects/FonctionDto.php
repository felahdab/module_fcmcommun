<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;

use Modules\FcmCommun\DataObjects\CompetenceDto;

class FonctionDto extends Data
{
    public string $id;
    public string $libelle_long;
    public string $libelle_court;
    public string $url;

    /** @var Collection<int, CompetenceDto> */
    public Collection $competences;
    
}
