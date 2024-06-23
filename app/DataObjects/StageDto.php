<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;

class StageDto extends Data
{
    public string $id;
    public string $libelle_court;  
    public string $libelle_long;
}
