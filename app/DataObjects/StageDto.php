<?php

declare(strict_types=1);

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;

class StageDto extends Data
{
    public function __construct(
        public string $stage_libcourt,
        public string $stage_liblong
    ) {}
}
