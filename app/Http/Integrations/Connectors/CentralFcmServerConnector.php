<?php

namespace Modules\FcmCommun\Http\Integrations\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Http\Auth\TokenAuthenticator;

class CentralFcmServerConnector extends Connector
{
    use AcceptsJson;

    public function __construct(public readonly string $url, 
                                public readonly string $token) 
    {

    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator($this->token);
    }
    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        //return 'http://web/ffast/api/';
        return $this->url;
    }

    /**
     * Default headers for every request
     */
    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Default HTTP client options
     */
    protected function defaultConfig(): array
    {
        return [
            'verify' => false,
        ];
    }
}
