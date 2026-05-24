<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp;

use HenriqueAmrl\AsaasPhp\Http\HttpClient;

final class AsaasClient
{
    private readonly HttpClient $httpClient; // @phpstan-ignore property.onlyWritten

    public function __construct(
        string $apiKey,
        Environment $environment = Environment::Sandbox,
    ) {
        $this->httpClient = new HttpClient(
            apiKey: $apiKey,
            baseUrl: $environment->baseUrl(),
        );
    }
}
