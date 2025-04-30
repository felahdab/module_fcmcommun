<?php

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class UserGeneratedEvent extends Data
{
    public string $event_uuid;
    public  $record;

    public function __construct(
        public string|array $event_type,
        public string $user_uuid,
        public string|null $marin_uuid,
        public string|array $object_class,
        public string|array|null $object_uuid,
        public array $detail,
        public array $data,
        public string $prefixTable,
        $record,
    ) {
        $this->event_uuid = (string) Uuid::uuid4();
        $this->record = $record;

        // Validation des types
        if (!is_string($this->event_type) && !is_array($this->event_type)) {
            throw new \InvalidArgumentException('event_type doit être une chaîne ou un tableau.');
        }

        if (!is_string($this->object_class) && !is_array($this->object_class)) {
            throw new \InvalidArgumentException('object_class doit être une chaîne ou un tableau.');
        }

        if (!is_null($this->object_uuid) && !is_string($this->object_uuid) && !is_array($this->object_uuid)) {
            throw new \InvalidArgumentException('object_uuid doit être une chaîne, un tableau ou null.');
        }
    }

    public function isOfType(string $eventtype): bool
    {
        if (is_array($this->event_type)) {
            return in_array($eventtype, $this->event_type);
        }

        return Str::contains($this->event_type, $eventtype);
    }

    
}
