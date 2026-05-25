<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Integration;

use HenriqueAmrl\AsaasPhp\AsaasClient;
use HenriqueAmrl\AsaasPhp\Dto\Customer;
use HenriqueAmrl\AsaasPhp\Dto\PageResult;
use HenriqueAmrl\AsaasPhp\Environment;
use HenriqueAmrl\AsaasPhp\Exception\NotFoundException;
use HenriqueAmrl\AsaasPhp\Resource\CustomerResource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CustomerResourceIntegrationTest extends TestCase
{
    private CustomerResource $customers;

    /** @var list<string> */
    private array $createdIds = [];

    protected function setUp(): void
    {
        $apiKey = (string) (getenv('ASAAS_API_KEY') ?: ($_ENV['ASAAS_API_KEY'] ?? ''));

        if ($apiKey === '' || $apiKey === 'test_key') {
            $this->markTestSkipped('Integration tests require real ASAAS_API_KEY env var.');
        }

        $client = new AsaasClient($apiKey, Environment::Sandbox);
        $this->customers = $client->customers();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            try {
                $this->customers->delete($id);
            } catch (\Throwable) {
                // best-effort cleanup
            }
        }
    }

    #[Test]
    public function create_returns_customer_dto_with_api_assigned_id(): void
    {
        $customer = $this->customers->create([
            'name' => 'Integration Test Customer',
            'cpfCnpj' => (string) (getenv('ASAAS_TEST_CPF') ?: ($_ENV['ASAAS_TEST_CPF'] ?? '11144477735')),
            'email' => 'integration-test@example.com',
        ]);

        $this->createdIds[] = $customer->id;

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertNotEmpty($customer->id);
        $this->assertStringStartsWith('cus_', $customer->id);
        $this->assertSame('Integration Test Customer', $customer->name);
    }

    #[Test]
    public function find_returns_same_customer_created(): void
    {
        $created = $this->customers->create([
            'name' => 'Find Test Customer',
            'cpfCnpj' => (string) (getenv('ASAAS_TEST_CPF') ?: ($_ENV['ASAAS_TEST_CPF'] ?? '11144477735')),
        ]);
        $this->createdIds[] = $created->id;

        $found = $this->customers->find($created->id);

        $this->assertSame($created->id, $found->id);
        $this->assertSame('Find Test Customer', $found->name);
    }

    #[Test]
    public function update_returns_customer_with_new_name(): void
    {
        $created = $this->customers->create([
            'name' => 'Before Update',
            'cpfCnpj' => (string) (getenv('ASAAS_TEST_CPF') ?: ($_ENV['ASAAS_TEST_CPF'] ?? '11144477735')),
        ]);
        $this->createdIds[] = $created->id;

        $updated = $this->customers->update($created->id, ['name' => 'After Update']);

        $this->assertSame($created->id, $updated->id);
        $this->assertSame('After Update', $updated->name);
    }

    #[Test]
    public function delete_marks_customer_as_deleted(): void
    {
        $created = $this->customers->create([
            'name' => 'Delete Test Customer',
            'cpfCnpj' => (string) (getenv('ASAAS_TEST_CPF') ?: ($_ENV['ASAAS_TEST_CPF'] ?? '11144477735')),
        ]);

        $this->customers->delete($created->id);

        // Asaas soft-deletes: find still returns the customer with deleted=true
        try {
            $found = $this->customers->find($created->id);
            $this->assertTrue($found->deleted);
        } catch (\HenriqueAmrl\AsaasPhp\Exception\NotFoundException) {
            // hard delete also acceptable
            $this->assertTrue(true);
        }
    }

    #[Test]
    public function list_returns_page_result_with_customer_data(): void
    {
        $result = $this->customers->list([], 0, 5);

        $this->assertInstanceOf(PageResult::class, $result);
        $this->assertIsArray($result->data);
        $this->assertGreaterThanOrEqual(0, $result->totalCount);
        $this->assertLessThanOrEqual(5, count($result->data));

        foreach ($result->data as $customer) {
            $this->assertInstanceOf(Customer::class, $customer);
            $this->assertNotEmpty($customer->id);
        }
    }

    #[Test]
    public function list_with_name_filter_returns_matching_customers(): void
    {
        $unique = 'IntgTest-' . substr(md5((string) microtime(true)), 0, 8);
        $created = $this->customers->create([
            'name' => $unique,
            'cpfCnpj' => (string) (getenv('ASAAS_TEST_CPF') ?: ($_ENV['ASAAS_TEST_CPF'] ?? '11144477735')),
        ]);
        $this->createdIds[] = $created->id;

        $result = $this->customers->list(['name' => $unique]);

        $this->assertGreaterThanOrEqual(1, $result->totalCount);
        $ids = array_map(static fn (Customer $c): string => $c->id, $result->data);
        $this->assertContains($created->id, $ids);
    }

    #[Test]
    public function find_nonexistent_id_throws_not_found(): void
    {
        $this->expectException(NotFoundException::class);
        $this->customers->find('cus_nonexistent_id_xyz');
    }
}
