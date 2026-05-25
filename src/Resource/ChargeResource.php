<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Resource;

use HenriqueAmrl\AsaasPhp\Dto\BoletoIdentificationField;
use HenriqueAmrl\AsaasPhp\Dto\Charge;
use HenriqueAmrl\AsaasPhp\Dto\PageResult;
use HenriqueAmrl\AsaasPhp\Dto\PixQrCode;
use HenriqueAmrl\AsaasPhp\Enum\BillingType;

final class ChargeResource extends AbstractResource
{
    /**
     * @param array<string, mixed> $data
     * @see https://docs.asaas.com/reference/criar-nova-cobranca
     */
    public function createBoleto(array $data): Charge
    {
        $response = $this->httpClient->post('/payments', array_merge($data, ['billingType' => BillingType::Boleto->value]));

        return Charge::fromArray($response);
    }

    /**
     * @see https://docs.asaas.com/reference/recuperar-uma-unica-cobranca
     */
    public function find(string $id): Charge
    {
        $response = $this->httpClient->get('/payments/' . $id);

        return Charge::fromArray($response);
    }

    /**
     * @see https://docs.asaas.com/reference/retrieve-payment-identification-field
     */
    public function identificationField(string $id): BoletoIdentificationField
    {
        $response = $this->httpClient->get('/payments/' . $id . '/identificationField');

        return BoletoIdentificationField::fromArray($response);
    }

    /**
     * @param array<string, mixed> $data
     * @see https://docs.asaas.com/reference/criar-nova-cobranca
     */
    public function createPix(array $data): Charge
    {
        $response = $this->httpClient->post('/payments', array_merge($data, ['billingType' => BillingType::Pix->value]));

        return Charge::fromArray($response);
    }

    /**
     * @see https://docs.asaas.com/reference/get-pix-qr-code
     */
    public function pixQrCode(string $id): PixQrCode
    {
        $response = $this->httpClient->get('/payments/' . $id . '/pixQrCode');

        return PixQrCode::fromArray($response);
    }

    /**
     * Caller MUST supply either: (a) creditCard + creditCardHolderInfo + remoteIp, OR (b) creditCardToken.
     *
     * @param array<string, mixed> $data
     * @see https://docs.asaas.com/docs/payments-via-credit-card
     */
    public function createCreditCard(array $data): Charge
    {
        $response = $this->httpClient->post('/payments', array_merge($data, ['billingType' => BillingType::CreditCard->value]));

        return Charge::fromArray($response);
    }

    /**
     * @see https://docs.asaas.com/reference/delete-payment
     */
    public function cancel(string $id): void
    {
        $this->httpClient->delete('/payments/' . $id);
    }

    /**
     * Asaas uses POST (not DELETE) for refunds; POST is never retried by HttpClient.
     *
     * @see https://docs.asaas.com/reference/refund-payment
     */
    public function refund(string $id, ?float $value = null, ?string $description = null): void
    {
        $body = array_filter([
            'value' => $value,
            'description' => $description,
        ], static fn (mixed $v): bool => $v !== null);

        $this->httpClient->post('/payments/' . $id . '/refund', $body);
    }

    /**
     * @param array<string, string|int|bool> $filters
     * @return PageResult<Charge>
     * @see https://docs.asaas.com/reference/list-payments
     */
    public function list(array $filters = [], int $offset = 0, int $limit = 10): PageResult
    {
        $params = array_merge($filters, ['offset' => $offset, 'limit' => $limit]);
        $qs = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $qs = str_replace(['%5B', '%5D'], ['[', ']'], $qs);
        $response = $this->httpClient->get('/payments?' . $qs);

        /** @var array<int, array<string, mixed>> $rawItems */
        $rawItems = $response['data'] ?? [];

        return new PageResult(
            totalCount: (int) ($response['totalCount'] ?? 0),
            hasMore: (bool) ($response['hasMore'] ?? false),
            limit: (int) ($response['limit'] ?? $limit),
            offset: (int) ($response['offset'] ?? $offset),
            data: array_map(
                static fn (array $item): Charge => Charge::fromArray($item),
                $rawItems,
            ),
        );
    }
}
