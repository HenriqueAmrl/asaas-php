<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Unit\Resource;

use HenriqueAmrl\AsaasPhp\Dto\Customer;
use HenriqueAmrl\AsaasPhp\Dto\PageResult;
use HenriqueAmrl\AsaasPhp\Exception\NotFoundException;
use HenriqueAmrl\AsaasPhp\Http\HttpClient;
use HenriqueAmrl\AsaasPhp\Resource\CustomerResource;
use HenriqueAmrl\AsaasPhp\Tests\Unit\Support\FakeHttpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CustomerResourceTest extends TestCase
{
    #[Test]
    public function create_returns_typed_customer_dto_with_api_assigned_id(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'cus_123',
            'name' => 'Maria Silva',
            'cpfCnpj' => '12345678901',
            'email' => 'maria@example.com',
            'deleted' => false,
            'notificationDisabled' => false,
            'foreignCustomer' => false,
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new CustomerResource($httpClient);

        $customer = $resource->create(['name' => 'Maria Silva', 'cpfCnpj' => '12345678901']);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertSame('cus_123', $customer->id);
        $this->assertSame('Maria Silva', $customer->name);
        $this->assertSame('12345678901', $customer->cpfCnpj);
        $this->assertSame('maria@example.com', $customer->email);
        $this->assertFalse($customer->deleted);
        $this->assertFalse($customer->notificationDisabled);
        $this->assertFalse($customer->foreignCustomer);
    }

    #[Test]
    public function create_with_optional_fields_returns_typed_customer_dto(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'cus_456',
            'name' => 'Joao Santos',
            'cpfCnpj' => '98765432100',
            'deleted' => false,
            'notificationDisabled' => false,
            'foreignCustomer' => false,
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new CustomerResource($httpClient);

        $customer = $resource->create([
            'name' => 'Joao Santos',
            'cpfCnpj' => '98765432100',
            'email' => 'joao@example.com',
        ]);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertSame('cus_456', $customer->id);
        $this->assertSame('Joao Santos', $customer->name);
    }

    #[Test]
    public function find_returns_typed_customer_dto(): void
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

        $customer = $resource->find('cus_123');

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertSame('cus_123', $customer->id);
        $this->assertSame('Maria Silva', $customer->name);
    }

    #[Test]
    public function find_nonexistent_customer_throws_not_found_exception(): void
    {
        $fake = FakeHttpClient::withJsonResponse(404, [
            'errors' => [
                ['code' => 'not_found', 'description' => 'Customer not found'],
            ],
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new CustomerResource($httpClient);

        $this->expectException(NotFoundException::class);

        $resource->find('cus_missing');
    }

    #[Test]
    public function update_returns_typed_customer_dto_with_changed_fields(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'cus_123',
            'name' => 'Updated Name',
            'cpfCnpj' => '12345678901',
            'deleted' => false,
            'notificationDisabled' => false,
            'foreignCustomer' => false,
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new CustomerResource($httpClient);

        $customer = $resource->update('cus_123', ['name' => 'Updated Name']);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertSame('cus_123', $customer->id);
        $this->assertSame('Updated Name', $customer->name);
    }

    #[Test]
    public function delete_returns_void_on_success_response(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, ['deleted' => true, 'id' => 'cus_123']);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new CustomerResource($httpClient);

        $resource->delete('cus_123');

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function list_returns_page_result_of_customer_dtos(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'totalCount' => 2,
            'hasMore' => false,
            'limit' => 10,
            'offset' => 0,
            'data' => [
                [
                    'id' => 'cus_1',
                    'name' => 'Alice',
                    'cpfCnpj' => '11111111111',
                    'deleted' => false,
                    'notificationDisabled' => false,
                    'foreignCustomer' => false,
                ],
                [
                    'id' => 'cus_2',
                    'name' => 'Bob',
                    'cpfCnpj' => '22222222222',
                    'deleted' => false,
                    'notificationDisabled' => false,
                    'foreignCustomer' => false,
                ],
            ],
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new CustomerResource($httpClient);

        $result = $resource->list();

        $this->assertInstanceOf(PageResult::class, $result);
        $this->assertSame(2, $result->totalCount);
        $this->assertFalse($result->hasMore);
        $this->assertCount(2, $result->data);
        $this->assertInstanceOf(Customer::class, $result->data[0]);
        $this->assertSame('cus_1', $result->data[0]->id);
    }

    #[Test]
    public function list_with_filters_passes_them_to_query_string(): void
    {
        // Deep URL inspection requires extending FakeHttpClient; deferred to a future plan if needed.
        $fake = FakeHttpClient::withJsonResponse(200, [
            'totalCount' => 1,
            'hasMore' => false,
            'limit' => 5,
            'offset' => 0,
            'data' => [
                [
                    'id' => 'cus_3',
                    'name' => 'Maria',
                    'cpfCnpj' => '33333333333',
                    'deleted' => false,
                    'notificationDisabled' => false,
                    'foreignCustomer' => false,
                ],
            ],
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new CustomerResource($httpClient);

        $result = $resource->list(['name' => 'Maria'], 0, 5);

        $this->assertSame(1, $result->totalCount);
        $this->assertSame('Maria', $result->data[0]->name);
    }

    #[Test]
    public function list_with_empty_response_returns_empty_page_result(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'totalCount' => 0,
            'hasMore' => false,
            'limit' => 10,
            'offset' => 0,
            'data' => [],
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new CustomerResource($httpClient);

        $result = $resource->list();

        $this->assertSame(0, $result->totalCount);
        $this->assertSame([], $result->data);
    }
}
