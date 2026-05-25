<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp;

use HenriqueAmrl\AsaasPhp\Http\HttpClient;
use HenriqueAmrl\AsaasPhp\Resource\ChargeResource;
use HenriqueAmrl\AsaasPhp\Resource\CustomerResource;

final class AsaasClient
{
    private readonly HttpClient $httpClient;

    private ?CustomerResource $customers = null;

    private ?ChargeResource $charges = null;

    public function __construct(
        string $apiKey,
        Environment $environment = Environment::Sandbox,
    ) {
        $this->httpClient = new HttpClient(
            apiKey: $apiKey,
            baseUrl: $environment->baseUrl(),
        );
    }

    public function customers(): CustomerResource
    {
        return $this->customers ??= new CustomerResource($this->httpClient);
    }

    public function charges(): ChargeResource
    {
        return $this->charges ??= new ChargeResource($this->httpClient);
    }
}
