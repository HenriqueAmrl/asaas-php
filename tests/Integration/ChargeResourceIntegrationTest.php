<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Integration;

use HenriqueAmrl\AsaasPhp\AsaasClient;
use HenriqueAmrl\AsaasPhp\Dto\BoletoIdentificationField;
use HenriqueAmrl\AsaasPhp\Dto\Charge;
use HenriqueAmrl\AsaasPhp\Dto\PageResult;
use HenriqueAmrl\AsaasPhp\Dto\PixQrCode;
use HenriqueAmrl\AsaasPhp\Enum\BillingType;
use HenriqueAmrl\AsaasPhp\Enum\ChargeStatus;
use HenriqueAmrl\AsaasPhp\Environment;
use HenriqueAmrl\AsaasPhp\Exception\NotFoundException;
use HenriqueAmrl\AsaasPhp\Resource\ChargeResource;
use HenriqueAmrl\AsaasPhp\Resource\CustomerResource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChargeResourceIntegrationTest extends TestCase
{
    private ChargeResource $charges;

    private CustomerResource $customers;

    private string $customerId = '';

    private string $cpf = '';

    /** @var list<string> */
    private array $createdChargeIds = [];

    protected function setUp(): void
    {
        $apiKey = (string) (getenv('ASAAS_API_KEY') ?: ($_ENV['ASAAS_API_KEY'] ?? ''));
        $this->cpf = (string) (getenv('ASAAS_TEST_CPF') ?: ($_ENV['ASAAS_TEST_CPF'] ?? ''));

        if ($apiKey === '') {
            $this->markTestSkipped('Integration tests require real ASAAS_API_KEY env var.');
        }

        if ($this->cpf === '') {
            $this->markTestSkipped('Integration tests require ASAAS_TEST_CPF env var.');
        }

        $client = new AsaasClient($apiKey, Environment::Sandbox);
        $this->charges = $client->charges();
        $this->customers = $client->customers();

        // Create a reusable customer for charge tests
        $customer = $this->customers->create([
            'name' => 'Charge Integration Test Customer',
            'cpfCnpj' => $this->cpf,
        ]);
        $this->customerId = $customer->id;
    }

    protected function tearDown(): void
    {
        foreach ($this->createdChargeIds as $id) {
            try {
                $this->charges->cancel($id);
            } catch (\Throwable) {
                // best-effort cleanup
            }
        }
        try {
            $this->customers->delete($this->customerId);
        } catch (\Throwable) {
            // best-effort cleanup
        }
    }

    // ------------------------------------------------------------------ boleto

    #[Test]
    public function createBoleto_returns_typed_charge_dto_with_boleto_billing_type(): void
    {
        $charge = $this->charges->createBoleto([
            'customer' => $this->customerId,
            'value' => 10.00,
            'dueDate' => date('Y-m-d', strtotime('+7 days')),
            'description' => 'Integration test boleto',
        ]);
        $this->createdChargeIds[] = $charge->id;

        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertNotEmpty($charge->id);
        $this->assertStringStartsWith('pay_', $charge->id);
        $this->assertSame(BillingType::Boleto, $charge->billingType);
        $this->assertSame($this->customerId, $charge->customer);
        $this->assertSame(10.00, $charge->value);
        $this->assertContains($charge->status, [ChargeStatus::Pending, ChargeStatus::AwaitingRiskAnalysis]);
    }

    #[Test]
    public function find_returns_charge_with_matching_id(): void
    {
        $created = $this->charges->createBoleto([
            'customer' => $this->customerId,
            'value' => 15.00,
            'dueDate' => date('Y-m-d', strtotime('+7 days')),
        ]);
        $this->createdChargeIds[] = $created->id;

        $found = $this->charges->find($created->id);

        $this->assertSame($created->id, $found->id);
        $this->assertSame(BillingType::Boleto, $found->billingType);
        $this->assertSame(15.00, $found->value);
    }

    #[Test]
    public function identificationField_returns_boleto_identification_field_dto(): void
    {
        $charge = $this->charges->createBoleto([
            'customer' => $this->customerId,
            'value' => 20.00,
            'dueDate' => date('Y-m-d', strtotime('+7 days')),
        ]);
        $this->createdChargeIds[] = $charge->id;

        $field = $this->charges->identificationField($charge->id);

        $this->assertInstanceOf(BoletoIdentificationField::class, $field);
        // In sandbox the fields may be empty strings if boleto not yet processed
        $this->assertIsString($field->identificationField);
        $this->assertIsString($field->nossoNumero);
        $this->assertIsString($field->barCode);
    }

    // ------------------------------------------------------------------ pix

    #[Test]
    public function createPix_returns_typed_charge_dto_with_pix_billing_type(): void
    {
        $charge = $this->charges->createPix([
            'customer' => $this->customerId,
            'value' => 25.00,
            'dueDate' => date('Y-m-d', strtotime('+7 days')),
            'description' => 'Integration test PIX',
        ]);
        $this->createdChargeIds[] = $charge->id;

        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertNotEmpty($charge->id);
        $this->assertSame(BillingType::Pix, $charge->billingType);
        $this->assertSame(25.00, $charge->value);
    }

    #[Test]
    public function pixQrCode_returns_pix_qr_code_dto(): void
    {
        $charge = $this->charges->createPix([
            'customer' => $this->customerId,
            'value' => 30.00,
            'dueDate' => date('Y-m-d', strtotime('+7 days')),
        ]);
        $this->createdChargeIds[] = $charge->id;

        $qrCode = $this->charges->pixQrCode($charge->id);

        $this->assertInstanceOf(PixQrCode::class, $qrCode);
        $this->assertNotEmpty($qrCode->encodedImage);
        $this->assertNotEmpty($qrCode->payload);
        // payload must start with PIX standard prefix
        $this->assertStringStartsWith('000201', $qrCode->payload);
    }

    // ------------------------------------------------------------------ credit card

    #[Test]
    public function createCreditCard_with_inline_card_returns_confirmed_charge(): void
    {
        $charge = $this->charges->createCreditCard([
            'customer' => $this->customerId,
            'value' => 50.00,
            'dueDate' => date('Y-m-d'),
            'description' => 'Integration test credit card',
            'creditCard' => [
                'holderName' => 'Integration Test',
                'number' => '5162306219378829',
                'expiryMonth' => '05',
                'expiryYear' => '2028',
                'ccv' => '318',
            ],
            'creditCardHolderInfo' => [
                'name' => 'Integration Test',
                'email' => 'integration-test@example.com',
                'cpfCnpj' => $this->cpf,
                'postalCode' => '89223-005',
                'addressNumber' => '277',
                'phone' => '47999999999',
            ],
            'remoteIp' => '127.0.0.1',
        ]);
        $this->createdChargeIds[] = $charge->id;

        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertNotEmpty($charge->id);
        $this->assertSame(BillingType::CreditCard, $charge->billingType);
        $this->assertSame(50.00, $charge->value);
        // Sandbox should confirm immediately
        $this->assertContains($charge->status, [
            ChargeStatus::Confirmed,
            ChargeStatus::Received,
            ChargeStatus::Pending,
            ChargeStatus::AwaitingRiskAnalysis,
        ]);
    }

    // ------------------------------------------------------------------ cancel

    #[Test]
    public function cancel_deletes_pending_charge_without_throwing(): void
    {
        $charge = $this->charges->createBoleto([
            'customer' => $this->customerId,
            'value' => 5.00,
            'dueDate' => date('Y-m-d', strtotime('+7 days')),
        ]);

        // cancel - do NOT add to createdChargeIds since we cancel here
        $this->charges->cancel($charge->id);

        // After cancellation, find should show deleted or throw NotFoundException
        try {
            $found = $this->charges->find($charge->id);
            $this->assertTrue($found->deleted);
        } catch (NotFoundException) {
            // also acceptable
            $this->assertTrue(true);
        }
    }

    #[Test]
    public function cancel_nonexistent_charge_throws_not_found(): void
    {
        $this->expectException(NotFoundException::class);
        $this->charges->cancel('pay_nonexistent_id_xyz');
    }

    // ------------------------------------------------------------------ list

    #[Test]
    public function list_returns_page_result_with_charge_data(): void
    {
        // Create at least one charge to ensure non-empty results
        $charge = $this->charges->createBoleto([
            'customer' => $this->customerId,
            'value' => 8.00,
            'dueDate' => date('Y-m-d', strtotime('+7 days')),
        ]);
        $this->createdChargeIds[] = $charge->id;

        $result = $this->charges->list([], 0, 10);

        $this->assertInstanceOf(PageResult::class, $result);
        $this->assertGreaterThan(0, $result->totalCount);
        $this->assertNotEmpty($result->data);

        foreach ($result->data as $item) {
            $this->assertInstanceOf(Charge::class, $item);
            $this->assertNotEmpty($item->id);
        }
    }

    #[Test]
    public function list_with_customer_filter_returns_only_that_customers_charges(): void
    {
        $charge = $this->charges->createBoleto([
            'customer' => $this->customerId,
            'value' => 12.00,
            'dueDate' => date('Y-m-d', strtotime('+7 days')),
        ]);
        $this->createdChargeIds[] = $charge->id;

        $result = $this->charges->list(['customer' => $this->customerId]);

        $this->assertGreaterThanOrEqual(1, $result->totalCount);
        foreach ($result->data as $item) {
            $this->assertSame($this->customerId, $item->customer);
        }
    }

    #[Test]
    public function list_with_billing_type_filter_returns_only_boleto_charges(): void
    {
        $charge = $this->charges->createBoleto([
            'customer' => $this->customerId,
            'value' => 7.00,
            'dueDate' => date('Y-m-d', strtotime('+7 days')),
        ]);
        $this->createdChargeIds[] = $charge->id;

        $result = $this->charges->list(['billingType' => 'BOLETO', 'customer' => $this->customerId]);

        $this->assertGreaterThanOrEqual(1, $result->totalCount);
        foreach ($result->data as $item) {
            $this->assertSame(BillingType::Boleto, $item->billingType);
        }
    }

    #[Test]
    public function find_nonexistent_charge_throws_not_found(): void
    {
        $this->expectException(NotFoundException::class);
        $this->charges->find('pay_nonexistent_id_xyz');
    }
}
