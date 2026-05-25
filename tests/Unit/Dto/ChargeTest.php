<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Unit\Dto;

use HenriqueAmrl\AsaasPhp\Dto\Charge;
use HenriqueAmrl\AsaasPhp\Enum\BillingType;
use HenriqueAmrl\AsaasPhp\Enum\ChargeStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChargeTest extends TestCase
{
    #[Test]
    public function fromArray_with_minimum_boleto_payload_returns_typed_dto_with_enum_values(): void
    {
        $charge = Charge::fromArray([
            'id' => 'pay_123',
            'customer' => 'cus_1',
            'billingType' => 'BOLETO',
            'status' => 'PENDING',
            'value' => 100.0,
            'dueDate' => '2026-06-01',
        ]);

        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertSame('pay_123', $charge->id);
        $this->assertSame('cus_1', $charge->customer);
        $this->assertSame(BillingType::Boleto, $charge->billingType);
        $this->assertSame(ChargeStatus::Pending, $charge->status);
        $this->assertSame(100.0, $charge->value);
        $this->assertSame('2026-06-01', $charge->dueDate);
    }

    #[Test]
    public function fromArray_with_unknown_billing_type_returns_null_billing_type(): void
    {
        $charge = Charge::fromArray([
            'id' => 'pay_456',
            'customer' => 'cus_1',
            'billingType' => 'FUTURE_METHOD',
            'status' => 'PENDING',
            'value' => 50.0,
            'dueDate' => '2026-06-01',
        ]);

        $this->assertNull($charge->billingType);
    }

    #[Test]
    public function fromArray_with_unknown_status_returns_null_status(): void
    {
        $charge = Charge::fromArray([
            'id' => 'pay_789',
            'customer' => 'cus_1',
            'billingType' => 'BOLETO',
            'status' => 'UNKNOWN',
            'value' => 75.0,
            'dueDate' => '2026-06-01',
        ]);

        $this->assertNull($charge->status);
    }

    #[Test]
    public function fromArray_populates_credit_card_token_when_present(): void
    {
        $charge = Charge::fromArray([
            'id' => 'pay_101',
            'customer' => 'cus_1',
            'billingType' => 'CREDIT_CARD',
            'status' => 'CONFIRMED',
            'value' => 200.0,
            'dueDate' => '2026-06-01',
            'creditCardToken' => 'tok_xyz',
        ]);

        $this->assertSame('tok_xyz', $charge->creditCardToken);
    }

    #[Test]
    public function fromArray_with_missing_optional_fields_returns_null_for_those_fields(): void
    {
        $charge = Charge::fromArray([
            'id' => 'pay_min',
            'customer' => 'cus_1',
            'billingType' => 'BOLETO',
            'status' => 'PENDING',
            'value' => 100.0,
            'dueDate' => '2026-06-01',
        ]);

        $this->assertNull($charge->description);
        $this->assertNull($charge->externalReference);
        $this->assertNull($charge->invoiceUrl);
        $this->assertNull($charge->bankSlipUrl);
        $this->assertNull($charge->creditCardToken);
        $this->assertNull($charge->nossoNumero);
        $this->assertNull($charge->netValue);
        $this->assertFalse($charge->deleted);
        $this->assertFalse($charge->postalService);
        $this->assertFalse($charge->anticipated);
        $this->assertFalse($charge->anticipable);
    }
}
