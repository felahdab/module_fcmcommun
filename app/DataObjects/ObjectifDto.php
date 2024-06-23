<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;

class ObjectifDto extends Data
{
    public string $id;
    public string $libelle_long;
    public string $libelle_court;
    
    
}
