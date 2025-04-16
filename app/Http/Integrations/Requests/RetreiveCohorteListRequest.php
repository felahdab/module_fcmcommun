<?php

namespace Modules\FcmCommun\Http\Integrations\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class RetreiveCohorteListRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct() {
        //
    }

    public function resolveEndpoint(): string
    {
        return '/fcmcentral/v1/cohortes';
    }
}