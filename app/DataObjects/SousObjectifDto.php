<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;

class SousObjectifDto extends Data
{
    public string $ssobj_lib;  
    public float $ssobj_coeff;
    public float $ssobj_duree;
}
