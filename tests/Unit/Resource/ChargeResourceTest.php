<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Unit\Resource;

use HenriqueAmrl\AsaasPhp\AsaasClient;
use HenriqueAmrl\AsaasPhp\Dto\BoletoIdentificationField;
use HenriqueAmrl\AsaasPhp\Dto\Charge;
use HenriqueAmrl\AsaasPhp\Dto\PageResult;
use HenriqueAmrl\AsaasPhp\Dto\PixQrCode;
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

    #[Test]
    public function createPix_returns_typed_charge_dto_with_pix_billing_type(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'pay_456',
            'customer' => 'cus_2',
            'billingType' => 'PIX',
            'status' => 'PENDING',
            'value' => 50.0,
            'dueDate' => '2026-06-15',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $charge = $resource->createPix([
            'customer' => 'cus_2',
            'value' => 50.0,
            'dueDate' => '2026-06-15',
        ]);

        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertSame(BillingType::Pix, $charge->billingType);
        $this->assertSame(ChargeStatus::Pending, $charge->status);
        $this->assertSame(50.0, $charge->value);
    }

    #[Test]
    public function pixQrCode_returns_typed_pix_qr_code_dto(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'encodedImage' => 'iVBORw0KGgo',
            'payload' => '00020126580014BR.GOV.BCB.PIX',
            'expirationDate' => '2027-06-15',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $qrCode = $resource->pixQrCode('pay_456');

        $this->assertInstanceOf(PixQrCode::class, $qrCode);
        $this->assertSame('iVBORw0KGgo', $qrCode->encodedImage);
        $this->assertSame('00020126580014BR.GOV.BCB.PIX', $qrCode->payload);
        $this->assertSame('2027-06-15', $qrCode->expirationDate);
    }

    #[Test]
    public function createCreditCard_with_inline_card_payload_returns_typed_charge_with_token(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'pay_789',
            'customer' => 'cus_3',
            'billingType' => 'CREDIT_CARD',
            'status' => 'CONFIRMED',
            'value' => 200.0,
            'dueDate' => '2026-06-20',
            'creditCardToken' => 'tok_abc123',
            'creditCardNumber' => '1234',
            'creditCardBrand' => 'VISA',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $charge = $resource->createCreditCard([
            'customer' => 'cus_3',
            'value' => 200.0,
            'dueDate' => '2026-06-20',
            'creditCard' => [
                'holderName' => 'Maria Silva',
                'number' => '5162306219378829',
                'expiryMonth' => '05',
                'expiryYear' => '2028',
                'ccv' => '318',
            ],
            'creditCardHolderInfo' => [
                'name' => 'Maria Silva',
                'email' => 'maria@example.com',
                'cpfCnpj' => '24971563792',
                'postalCode' => '89223-005',
                'addressNumber' => '277',
            ],
            'remoteIp' => '203.0.113.42',
        ]);

        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertSame(BillingType::CreditCard, $charge->billingType);
        $this->assertSame(ChargeStatus::Confirmed, $charge->status);
        $this->assertSame('tok_abc123', $charge->creditCardToken);
        $this->assertSame('VISA', $charge->creditCardBrand);
    }

    #[Test]
    public function createCreditCard_with_token_only_payload_returns_typed_charge(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'pay_999',
            'customer' => 'cus_4',
            'billingType' => 'CREDIT_CARD',
            'status' => 'CONFIRMED',
            'value' => 75.0,
            'dueDate' => '2026-07-01',
            'creditCardToken' => 'tok_abc123',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $charge = $resource->createCreditCard([
            'customer' => 'cus_4',
            'value' => 75.0,
            'dueDate' => '2026-07-01',
            'creditCardToken' => 'tok_abc123',
        ]);

        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertSame(BillingType::CreditCard, $charge->billingType);
        $this->assertSame('tok_abc123', $charge->creditCardToken);
    }

    #[Test]
    public function createPix_injects_pix_billing_type_into_outbound_request_body(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'pay_x',
            'customer' => 'cus_x',
            'billingType' => 'PIX',
            'status' => 'PENDING',
            'value' => 10.0,
            'dueDate' => '2026-08-01',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $resource->createPix([
            'customer' => 'cus_x',
            'value' => 10.0,
            'dueDate' => '2026-08-01',
            'billingType' => 'BOLETO',
        ]);

        $requestBody = $fake->getLastRequestBody();
        $this->assertSame('PIX', $requestBody['billingType']);
    }

    #[Test]
    public function cancel_returns_void_on_success_response(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'deleted' => true,
            'id' => 'pay_123',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $resource->cancel('pay_123');

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function cancel_nonexistent_charge_throws_not_found_exception(): void
    {
        $fake = FakeHttpClient::withJsonResponse(404, [
            'errors' => [
                ['code' => 'not_found', 'description' => 'Payment not found'],
            ],
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $this->expectException(NotFoundException::class);

        $resource->cancel('pay_missing');
    }

    #[Test]
    public function refund_with_no_arguments_returns_void_full_refund(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'pay_123',
            'status' => 'REFUNDED',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $resource->refund('pay_123');

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function refund_with_value_returns_void_partial_refund(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'pay_123',
            'status' => 'REFUND_REQUESTED',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $resource->refund('pay_123', value: 50.0);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function refund_with_value_and_description_returns_void(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'pay_123',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $resource->refund('pay_123', value: 50.0, description: 'goodwill partial');

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function refund_with_description_only_returns_void(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'pay_123',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $resource->refund('pay_123', description: 'goodwill full');

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function list_returns_page_result_of_charge_dtos(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'totalCount' => 2,
            'hasMore' => false,
            'limit' => 10,
            'offset' => 0,
            'data' => [
                [
                    'id' => 'pay_1',
                    'customer' => 'cus_a',
                    'billingType' => 'BOLETO',
                    'status' => 'PENDING',
                    'value' => 100.0,
                    'dueDate' => '2026-06-01',
                ],
                [
                    'id' => 'pay_2',
                    'customer' => 'cus_b',
                    'billingType' => 'PIX',
                    'status' => 'CONFIRMED',
                    'value' => 50.0,
                    'dueDate' => '2026-06-15',
                ],
            ],
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $result = $resource->list();

        $this->assertInstanceOf(PageResult::class, $result);
        $this->assertSame(2, $result->totalCount);
        $this->assertFalse($result->hasMore);
        $this->assertCount(2, $result->data);
        $this->assertInstanceOf(Charge::class, $result->data[0]);
        $this->assertSame('pay_1', $result->data[0]->id);
        $this->assertSame(BillingType::Pix, $result->data[1]->billingType);
    }

    #[Test]
    public function list_with_customer_and_status_filters_returns_filtered_page_result(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'totalCount' => 1,
            'hasMore' => false,
            'limit' => 10,
            'offset' => 0,
            'data' => [
                [
                    'id' => 'pay_3',
                    'customer' => 'cus_a',
                    'billingType' => 'BOLETO',
                    'status' => 'PENDING',
                    'value' => 75.0,
                    'dueDate' => '2026-07-01',
                ],
            ],
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $result = $resource->list(['customer' => 'cus_a', 'status' => 'PENDING']);

        $this->assertSame(1, $result->totalCount);
        $this->assertSame('cus_a', $result->data[0]->customer);
        $this->assertSame(ChargeStatus::Pending, $result->data[0]->status);
    }

    #[Test]
    public function list_with_due_date_range_filters_returns_typed_page_result(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'totalCount' => 1,
            'hasMore' => false,
            'limit' => 10,
            'offset' => 0,
            'data' => [
                [
                    'id' => 'pay_4',
                    'customer' => 'cus_b',
                    'billingType' => 'PIX',
                    'status' => 'PENDING',
                    'value' => 200.0,
                    'dueDate' => '2026-06-15',
                ],
            ],
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $result = $resource->list(['dueDate[ge]' => '2026-01-01', 'dueDate[le]' => '2026-12-31']);

        $this->assertInstanceOf(PageResult::class, $result);
        $this->assertSame(1, $result->totalCount);
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
        $resource = new ChargeResource($httpClient);

        $result = $resource->list();

        $this->assertSame(0, $result->totalCount);
        $this->assertSame([], $result->data);
    }

    #[Test]
    public function list_with_custom_offset_and_limit_passes_them_through(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'totalCount' => 15,
            'hasMore' => true,
            'limit' => 5,
            'offset' => 10,
            'data' => [],
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $result = $resource->list([], 10, 5);

        $this->assertSame(5, $result->limit);
        $this->assertSame(10, $result->offset);
        $this->assertTrue($result->hasMore);
    }

    #[Test]
    public function list_with_bracket_filter_keys_includes_filter_keys_in_url(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'totalCount' => 0,
            'hasMore' => false,
            'limit' => 10,
            'offset' => 0,
            'data' => [],
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $resource->list(['dueDate[ge]' => '2026-01-01', 'dueDate[le]' => '2026-12-31']);

        // Decode the URI to compare filter keys regardless of bracket encoding by PSR-7
        $rawUri = urldecode((string) $fake->getLastRequest()->getUri());
        $this->assertStringContainsString('dueDate[ge]=2026-01-01', $rawUri);
        $this->assertStringContainsString('dueDate[le]=2026-12-31', $rawUri);
    }

    #[Test]
    public function cancel_sends_delete_request_to_correct_path(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'deleted' => true,
            'id' => 'pay_777',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $resource->cancel('pay_777');

        $request = $fake->getLastRequest();
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertStringEndsWith('/payments/pay_777', (string) $request->getUri());
    }

    #[Test]
    public function refund_sends_post_to_refund_path(): void
    {
        $fake = FakeHttpClient::withJsonResponse(200, [
            'id' => 'pay_888',
            'status' => 'REFUNDED',
        ]);
        $httpClient = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fake);
        $resource = new ChargeResource($httpClient);

        $resource->refund('pay_888', value: 30.0, description: 'test refund');

        $request = $fake->getLastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringEndsWith('/payments/pay_888/refund', (string) $request->getUri());
        $requestBody = $fake->getLastRequestBody();
        $this->assertSame(30, (int) $requestBody['value']);
        $this->assertSame('test refund', $requestBody['description']);
    }
}
