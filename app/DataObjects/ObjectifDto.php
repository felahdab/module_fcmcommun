<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;

use Modules\FcmCommun\DataObjects\SousObjectifDto;

class ObjectifDto extends Data
{
    
    public string $objectif_libcourt;
    public string $objectif_liblong;
    
    /** @var Collection<int, SousObjectifDto> */
    public Collection $sous_objectifs;
    
}
