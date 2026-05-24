<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Unit\Dto;

use HenriqueAmrl\AsaasPhp\Dto\Customer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CustomerTest extends TestCase
{
    #[Test]
    public function constructs_with_required_fields_and_exposes_readonly_properties(): void
    {
        $customer = new Customer(
            id: 'cus_1',
            name: 'X',
            cpfCnpj: '123',
        );

        $this->assertSame('cus_1', $customer->id);
        $this->assertSame('X', $customer->name);
        $this->assertSame('123', $customer->cpfCnpj);
    }

    #[Test]
    public function from_array_with_only_required_keys_returns_instance_with_null_optional_fields(): void
    {
        $customer = Customer::fromArray([
            'id' => 'cus_2',
            'name' => 'Test User',
            'cpfCnpj' => '987654321',
            'deleted' => false,
            'notificationDisabled' => false,
            'foreignCustomer' => false,
        ]);

        $this->assertSame('cus_2', $customer->id);
        $this->assertSame('Test User', $customer->name);
        $this->assertNull($customer->email);
        $this->assertNull($customer->phone);
        $this->assertNull($customer->mobilePhone);
        $this->assertNull($customer->address);
        $this->assertNull($customer->addressNumber);
        $this->assertNull($customer->complement);
        $this->assertNull($customer->province);
        $this->assertNull($customer->city);
        $this->assertNull($customer->cityName);
        $this->assertNull($customer->state);
        $this->assertNull($customer->country);
        $this->assertNull($customer->postalCode);
        $this->assertNull($customer->personType);
        $this->assertNull($customer->additionalEmails);
        $this->assertNull($customer->externalReference);
        $this->assertNull($customer->observations);
        $this->assertNull($customer->groupName);
        $this->assertNull($customer->municipalInscription);
        $this->assertNull($customer->stateInscription);
        $this->assertNull($customer->company);
        $this->assertNull($customer->object);
        $this->assertNull($customer->dateCreated);
    }

    #[Test]
    public function from_array_with_full_payload_populates_all_properties(): void
    {
        $customer = Customer::fromArray([
            'id' => 'cus_full',
            'name' => 'Full User',
            'cpfCnpj' => '11122233344',
            'email' => 'full@example.com',
            'phone' => '11999990000',
            'mobilePhone' => '11988880000',
            'address' => 'Rua Teste',
            'addressNumber' => '123',
            'complement' => 'Apto 1',
            'province' => 'Centro',
            'city' => 1234,
            'cityName' => 'Sao Paulo',
            'state' => 'SP',
            'country' => 'BR',
            'postalCode' => '01310100',
            'personType' => 'FISICA',
            'deleted' => true,
            'additionalEmails' => 'extra@example.com',
            'externalReference' => 'ext-ref-123',
            'notificationDisabled' => true,
            'observations' => 'Some notes',
            'foreignCustomer' => true,
            'groupName' => 'VIP',
            'municipalInscription' => 'MUN123',
            'stateInscription' => 'ST456',
            'company' => 'ACME Corp',
            'object' => 'customer',
            'dateCreated' => '2024-01-15',
        ]);

        $this->assertSame('cus_full', $customer->id);
        $this->assertSame('Full User', $customer->name);
        $this->assertSame('11122233344', $customer->cpfCnpj);
        $this->assertSame('full@example.com', $customer->email);
        $this->assertSame('11999990000', $customer->phone);
        $this->assertSame('11988880000', $customer->mobilePhone);
        $this->assertSame('Rua Teste', $customer->address);
        $this->assertSame('123', $customer->addressNumber);
        $this->assertSame('Apto 1', $customer->complement);
        $this->assertSame('Centro', $customer->province);
        $this->assertSame(1234, $customer->city);
        $this->assertSame('Sao Paulo', $customer->cityName);
        $this->assertSame('SP', $customer->state);
        $this->assertSame('BR', $customer->country);
        $this->assertSame('01310100', $customer->postalCode);
        $this->assertSame('FISICA', $customer->personType);
        $this->assertTrue($customer->deleted);
        $this->assertSame('extra@example.com', $customer->additionalEmails);
        $this->assertSame('ext-ref-123', $customer->externalReference);
        $this->assertTrue($customer->notificationDisabled);
        $this->assertSame('Some notes', $customer->observations);
        $this->assertTrue($customer->foreignCustomer);
        $this->assertSame('VIP', $customer->groupName);
        $this->assertSame('MUN123', $customer->municipalInscription);
        $this->assertSame('ST456', $customer->stateInscription);
        $this->assertSame('ACME Corp', $customer->company);
        $this->assertSame('customer', $customer->object);
        $this->assertSame('2024-01-15', $customer->dateCreated);
    }

    #[Test]
    public function from_array_with_missing_required_keys_defaults_to_empty_string_and_false(): void
    {
        $customer = Customer::fromArray([]);

        $this->assertSame('', $customer->id);
        $this->assertSame('', $customer->name);
        $this->assertSame('', $customer->cpfCnpj);
        $this->assertFalse($customer->deleted);
        $this->assertFalse($customer->notificationDisabled);
        $this->assertFalse($customer->foreignCustomer);
    }
}
