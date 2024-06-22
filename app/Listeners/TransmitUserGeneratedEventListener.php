<?php

namespace Modules\FcmCommun\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Modules\FcmCommun\DataObjects\UserGeneratedEvent;

use Illuminate\Support\Facades\Http;

class TransmitUserGeneratedEventListener
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
    public function handle(UserGeneratedEvent $event): void
    {
        return;
        if ($event->event_type == "user_receives_uuid"){
            return;
        }
        
        $url = "http://web/ffast/api/v1/postevent";
        $token="1|XBVAkgJFEGHlIV9R4vLlNVVg6x4iAIptfYi0if5y847a069d";

        $response = Http::withToken($token)
                ->post($url, $event->toArray());

    }
}
