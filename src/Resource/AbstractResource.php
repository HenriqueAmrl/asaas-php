<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Resource;

use HenriqueAmrl\AsaasPhp\Http\HttpClient;

abstract class AbstractResource
{
    public function __construct(
        protected readonly HttpClient $httpClient,
    ) {
    }

    /** @return array<string, mixed> */
    protected function get(string $path): array
    {
        return $this->httpClient->get($path);
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    protected function post(string $path, array $body = []): array
    {
        return $this->httpClient->post($path, $body);
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    protected function put(string $path, array $body = []): array
    {
        return $this->httpClient->put($path, $body);
    }

    /** @return array<string, mixed> */
    protected function delete(string $path): array
    {
        return $this->httpClient->delete($path);
    }
}
