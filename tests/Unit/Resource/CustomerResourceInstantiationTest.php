<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Unit\Resource;

use HenriqueAmrl\AsaasPhp\Dto\Customer;
use HenriqueAmrl\AsaasPhp\Http\HttpClient;
use HenriqueAmrl\AsaasPhp\Resource\CustomerResource;
use HenriqueAmrl\AsaasPhp\Tests\Unit\Support\FakeHttpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CustomerResource instantiation and basic create operation.
 * Task 2 RED tests: verify structure before implementation exists.
 */
final class CustomerResourceInstantiationTest extends TestCase
{
    #[Test]
    public function customer_resource_is_instantiable_with_http_client(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, []);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);

        $resource = new CustomerResource($httpClient);

        $this->assertInstanceOf(CustomerResource::class, $resource);
    }

    #[Test]
    public function create_maps_response_array_to_customer_dto(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'cus_123',
            'name' => 'Maria Silva',
            'cpfCnpj' => '12345678901',
            'deleted' => false,
            'notificationDisabled' => false,
            'foreignCustomer' => false,
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new CustomerResource($httpClient);

        $customer = $resource->create(['name' => 'Maria Silva', 'cpfCnpj' => '12345678901']);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertSame('cus_123', $customer->id);
    }
}
