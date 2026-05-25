<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Unit\Resource;

use HenriqueAmrl\AsaasPhp\AsaasClient;
use HenriqueAmrl\AsaasPhp\Dto\BoletoIdentificationField;
use HenriqueAmrl\AsaasPhp\Dto\Charge;
use HenriqueAmrl\AsaasPhp\Enum\BillingType;
use HenriqueAmrl\AsaasPhp\Enum\ChargeStatus;
use HenriqueAmrl\AsaasPhp\Exception\NotFoundException;
use HenriqueAmrl\AsaasPhp\Http\HttpClient;
use HenriqueAmrl\AsaasPhp\Resource\ChargeResource;
use HenriqueAmrl\AsaasPhp\Tests\Unit\Support\FakeHttpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChargeResourceTest extends TestCase
{
    #[Test]
    public function createBoleto_returns_typed_charge_dto_with_boleto_billing_type(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'pay_123',
            'customer' => 'cus_1',
            'billingType' => 'BOLETO',
            'status' => 'PENDING',
            'value' => 100.0,
            'dueDate' => '2026-06-01',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $charge = $resource->createBoleto([
            'customer' => 'cus_1',
            'value' => 100.0,
            'dueDate' => '2026-06-01',
        ]);

        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertSame('pay_123', $charge->id);
        $this->assertSame(BillingType::Boleto, $charge->billingType);
        $this->assertSame(ChargeStatus::Pending, $charge->status);
        $this->assertSame(100.0, $charge->value);
    }

    #[Test]
    public function createBoleto_with_optional_description_returns_typed_charge(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'pay_456',
            'customer' => 'cus_1',
            'billingType' => 'BOLETO',
            'status' => 'PENDING',
            'value' => 200.0,
            'dueDate' => '2026-06-15',
            'description' => 'Invoice #001',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $charge = $resource->createBoleto([
            'customer' => 'cus_1',
            'value' => 200.0,
            'dueDate' => '2026-06-15',
            'description' => 'Invoice #001',
        ]);

        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertSame('Invoice #001', $charge->description);
    }

    #[Test]
    public function find_returns_typed_charge_dto(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'pay_123',
            'customer' => 'cus_1',
            'billingType' => 'BOLETO',
            'status' => 'PENDING',
            'value' => 100.0,
            'dueDate' => '2026-06-01',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $charge = $resource->find('pay_123');

        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertSame('pay_123', $charge->id);
    }

    #[Test]
    public function find_nonexistent_charge_throws_not_found_exception(): void
    {
        $fake = FakeHttpClient::withJsonResponse(404, [
            'errors' => [
                ['code' => 'not_found', 'description' => 'Charge not found'],
            ],
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $this->expectException(NotFoundException::class);

        $resource->find('pay_missing');
    }

    #[Test]
    public function identificationField_returns_typed_boleto_identification_field_dto(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'identificationField' => '23793.38128 60008.181361 95000.063305 1 95760000010000',
            'nossoNumero' => '08000061',
            'barCode' => '23791957600000100005381286000818139500006330',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $field = $resource->identificationField('pay_123');

        $this->assertInstanceOf(BoletoIdentificationField::class, $field);
        $this->assertSame('23793.38128 60008.181361 95000.063305 1 95760000010000', $field->identificationField);
        $this->assertSame('08000061', $field->nossoNumero);
        $this->assertSame('23791957600000100005381286000818139500006330', $field->barCode);
    }

    #[Test]
    public function asaas_client_charges_accessor_returns_same_instance_on_repeat_calls(): void
    {
        $client = new AsaasClient('test_key');

        $first = $client->charges();
        $second = $client->charges();

        $this->assertInstanceOf(ChargeResource::class, $first);
        $this->assertSame($first, $second);
    }
}
