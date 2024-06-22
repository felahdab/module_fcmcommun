<?php

namespace Modules\FcmCommun\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCommun\DataObjects\RemoteGeneratedEvent;

use Modules\FcmCommun\Models\StoredEvent;

class StoreUserGeneratedEventListener
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
        StoredEvent::createFromUserGeneratedEvent($event);
    }
}
