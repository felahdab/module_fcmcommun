<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;

use Modules\FcmCommun\DataObjects\StageDto;
use Modules\FcmCommun\DataObjects\ObjectifDto;

class SavoirFaireDto extends Data
{
    public string $id;
    public string $libelle_long;
    public string $libelle_court;

    /** @var Collection<int, StageDto> */
    public Collection $stages;

    /** @var Collection<int, ObjectifDto> */
    public Collection $objectifs;
    
}
