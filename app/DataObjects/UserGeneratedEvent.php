<?php

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;

use Ramsey\Uuid\Uuid;

class UserGeneratedEvent extends Data
{
    public string $event_uuid;

    public function __construct(
        public string $event_type,
        public string $user_id,
        public string $object_class,
        public ?string $object_uuid,
        public array $detail
    ) {
        $this->event_uuid=(string) Uuid::uuid4();
    }
}