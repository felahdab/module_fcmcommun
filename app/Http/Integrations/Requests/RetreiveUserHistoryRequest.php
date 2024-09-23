<?php

namespace Modules\FcmCommun\Http\Integrations\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class RetreiveUserHistoryRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected readonly string $user_id) {
        //
    }

    public function resolveEndpoint(): string
    {
        return '/fcmcentral/v1/get_user_history/' . $this->user_id;
    }
}