<?php

namespace Modules\FcmCommun\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCommun\DataObjects\RemoteGeneratedEvent;

class LocalUserGeneratedEventListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserGeneratedEvent|RemoteGeneratedEvent $event): void
    {
        // if ($event->event_type == 'update'){
        //     $record = $event->object_class::find($event->object_uuid)->first();
        //     $record->update($event->detail);
        //     $record->save();
        // }

        // if ($event->event_type == 'create'){
        //     $record = $event->object_class::create($event->detail);
        // }
        
    }
}
