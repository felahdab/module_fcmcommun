<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;

use Modules\FcmCommun\DataObjects\TacheDto;

class CompagnonageDto extends Data
{
    
    public string $comp_libcourt;
    public string $comp_liblong;
    
    /** @var Collection<int, TacheDto> */
    public Collection $taches;
    
}
