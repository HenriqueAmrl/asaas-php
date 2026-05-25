<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Resource;

use HenriqueAmrl\AsaasPhp\Dto\BoletoIdentificationField;
use HenriqueAmrl\AsaasPhp\Dto\Charge;
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
     * @see https://docs.asaas.com/docs/cobrancas-via-boleto
     */
    public function identificationField(string $id): BoletoIdentificationField
    {
        $response = $this->httpClient->get('/payments/' . $id . '/identificationField');

        return BoletoIdentificationField::fromArray($response);
    }
}
