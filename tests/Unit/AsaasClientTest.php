<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Unit;

use HenriqueAmrl\AsaasPhp\AsaasClient;
use HenriqueAmrl\AsaasPhp\Environment;
use HenriqueAmrl\AsaasPhp\Http\HttpClient;
use HenriqueAmrl\AsaasPhp\Resource\AbstractResource;
use HenriqueAmrl\AsaasPhp\Resource\CustomerResource;
use HenriqueAmrl\AsaasPhp\Tests\Unit\Support\FakeHttpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AsaasClientTest extends TestCase
{
    #[Test]
    public function constructs_without_exception_with_api_key_and_sandbox_environment(): void
    {
        $client = new AsaasClient(apiKey: 'test_key');

        $this->assertInstanceOf(AsaasClient::class, $client);
    }

    #[Test]
    public function constructs_with_production_environment(): void
    {
        $client = new AsaasClient(apiKey: 'test_key', environment: Environment::Production);

        $this->assertInstanceOf(AsaasClient::class, $client);
    }

    #[Test]
    public function http_client_uses_injected_psr18_client_for_requests(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, ['foo' => 'bar']);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);

        $result = $httpClient->get('/test');

        $this->assertSame(['foo' => 'bar'], $result);
    }

    #[Test]
    public function injected_client_is_used_for_dispatch_through_resource(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, ['id' => 'cus_123']);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);

        $resource = new class ($httpClient) extends AbstractResource {
            /** @return array<string, mixed> */
            public function callGet(string $path): array
            {
                return $this->httpClient->get($path);
            }
        };

        $result = $resource->callGet('/customers');

        $this->assertSame(['id' => 'cus_123'], $result);
    }

    #[Test]
    public function customers_returns_customer_resource_instance(): void
    {
        $client = new AsaasClient(apiKey: 'test_key');

        $this->assertInstanceOf(CustomerResource::class, $client->customers());
    }

    #[Test]
    public function customers_returns_same_instance_on_repeated_calls(): void
    {
        $client = new AsaasClient(apiKey: 'test_key');

        $this->assertSame($client->customers(), $client->customers());
    }
}
