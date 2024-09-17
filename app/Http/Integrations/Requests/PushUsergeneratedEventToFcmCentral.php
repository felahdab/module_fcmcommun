<?php

namespace Modules\FcmCommun\Http\Integrations\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;

use Modules\FcmCentral\Events\UserGeneratedEvent;

class PushUsergeneratedEventToFcmCentral extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(public readonly UserGeneratedEvent $event) {
        //
    }

    public function resolveEndpoint(): string
    {
        return '/fcmcentral/v1/postevent';
    }

    protected function defaultBody(): array
    {
        return $this->event->toArray();
    }
}