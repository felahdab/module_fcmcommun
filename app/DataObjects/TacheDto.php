<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;

use Modules\FcmCommun\DataObjects\ObjectifDto;

class TacheDto extends Data
{
    
    public string $tache_libcourt;
    public string $tache_liblong;
    
    /** @var Collection<int, ObjectifDto> */
    public Collection $objectifs;
    
}
