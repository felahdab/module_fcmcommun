<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;

use Modules\FcmCommun\DataObjects\ActiviteDto;

class SavoirFaireDto extends Data
{
    public string $id;
    public string $libelle_long;
    public string $libelle_court;
    public string $url;
    public string $code_sicomp;
    public string $niveau;

    /** @var Collection<int, ActiviteDto> */
    public Collection $activites;
    
}
