<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp;

use HenriqueAmrl\AsaasPhp\Config\Environment;
use HenriqueAmrl\AsaasPhp\Http\HttpClient;
use Psr\Http\Client\ClientInterface;

final class AsaasClient
{
    private readonly HttpClient $httpClient;

    public function __construct(
        string $apiKey,
        Environment $environment = Environment::Sandbox,
        ?ClientInterface $httpClient = null,
    ) {
        $this->httpClient = new HttpClient(
            apiKey: $apiKey,
            baseUrl: $environment->baseUrl(),
            client: $httpClient,
        );
    }

    /**
     * Returns the internal HttpClient for use by resource accessor methods.
     *
     * @internal Used by resource accessor methods added in later work.
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    // Future resource accessors added here in later work:
    // public function customers(): CustomerResource
    // public function charges(): ChargeResource
    // public function subscriptions(): SubscriptionResource
    // public function pix(): PixResource
}
