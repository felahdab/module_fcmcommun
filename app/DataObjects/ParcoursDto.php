<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;

use Modules\FcmCommun\DataObjects\AnneeDto;

class ParcoursDto extends Data
{
    public string $id;
    public string $uuid;
    public string $libelle_long;
    public string $libelle_court;

    /** @var Collection<int, AnneeDto> */
    public Collection $annees;
    
}

