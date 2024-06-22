<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;

use Modules\FcmCommun\DataObjects\StageDto;
use Modules\FcmCommun\DataObjects\CompagnonageDto;

class FonctionDto extends Data
{
    
    public string $fonction_libcourt;
    public string $fonction_liblong;

    /** @var Collection<int, StageDto> */
    public Collection $stages;

    /** @var Collection<int, CompagnonageDto> */
    public Collection $compagnonages;
    
}
