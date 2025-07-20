<?php

namespace Modules\FcmCommun\DataObjects;

use Spatie\LaravelData\Data;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;

use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;

class UserGeneratedEvent extends Data
{
    public string $event_uuid;

    public function __construct(
        public string|UserGeneratedEventTypes $event_type,
        public string $user_uuid,
        public string|ObjectClasses $object_class,
        public string|null $object_uuid,
        public array $detail,
    ) {
        $this->event_uuid = (string) Uuid::uuid4();

        if ($event_type instanceof UserGeneratedEventTypes)
        {
            $this->event_type = $event_type->value;
        }

        if ($object_class instanceof ObjectClasses)
        {
            $this->object_class = $object_class->value;
        }
        // Cette ligne permet de vérifier que la valeur de $event_type est bien dans la liste des valeurs
        // autorisés dans l'énumération UserGeneratedEventTypes. Sinon, une exception est renvoyée.
        UserGeneratedEventTypes::from($this->event_type); 

        // Cette ligne permet de vérifier que la valeur de $object_class est bien dans la liste des valeurs
        // autorisés dans l'énumération ObjectClasses. Sinon, une exception est renvoyée.
        ObjectClasses::from($this->object_class);
    }

    public function isOfType(string|UserGeneratedEventTypes $eventtype): bool
    {
        if ($eventtype instanceof UserGeneratedEventTypes)
        {
            $eventtype = $eventtype->value;
        }

        return Str::contains($this->event_type, $eventtype);
    }

    
}
