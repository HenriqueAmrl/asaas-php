<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Dto;

use HenriqueAmrl\AsaasPhp\Enum\BillingType;
use HenriqueAmrl\AsaasPhp\Enum\ChargeStatus;

final class Charge
{
    public function __construct(
        public readonly string $id,
        public readonly string $customer,
        public readonly ?BillingType $billingType,
        public readonly ?ChargeStatus $status,
        public readonly float $value,
        public readonly string $dueDate,
        public readonly ?float $netValue = null,
        public readonly ?float $originalValue = null,
        public readonly ?float $interestValue = null,
        public readonly ?string $description = null,
        public readonly ?string $externalReference = null,
        public readonly ?string $invoiceUrl = null,
        public readonly ?string $invoiceNumber = null,
        public readonly ?string $bankSlipUrl = null,
        public readonly ?string $transactionReceiptUrl = null,
        public readonly ?string $nossoNumero = null,
        public readonly ?string $dateCreated = null,
        public readonly ?string $confirmedDate = null,
        public readonly ?string $paymentDate = null,
        public readonly ?string $clientPaymentDate = null,
        public readonly ?string $installmentNumber = null,
        public readonly ?string $creditCardToken = null,
        public readonly ?string $creditCardNumber = null,
        public readonly ?string $creditCardBrand = null,
        public readonly ?string $object = null,
        public readonly bool $deleted = false,
        public readonly bool $postalService = false,
        public readonly bool $anticipated = false,
        public readonly bool $anticipable = false,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            customer: (string) ($data['customer'] ?? ''),
            billingType: isset($data['billingType']) ? BillingType::tryFrom((string) $data['billingType']) : null,
            status: isset($data['status']) ? ChargeStatus::tryFrom((string) $data['status']) : null,
            value: (float) ($data['value'] ?? 0.0),
            dueDate: (string) ($data['dueDate'] ?? ''),
            netValue: isset($data['netValue']) ? (float) $data['netValue'] : null,
            originalValue: isset($data['originalValue']) ? (float) $data['originalValue'] : null,
            interestValue: isset($data['interestValue']) ? (float) $data['interestValue'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            externalReference: isset($data['externalReference']) ? (string) $data['externalReference'] : null,
            invoiceUrl: isset($data['invoiceUrl']) ? (string) $data['invoiceUrl'] : null,
            invoiceNumber: isset($data['invoiceNumber']) ? (string) $data['invoiceNumber'] : null,
            bankSlipUrl: isset($data['bankSlipUrl']) ? (string) $data['bankSlipUrl'] : null,
            transactionReceiptUrl: isset($data['transactionReceiptUrl']) ? (string) $data['transactionReceiptUrl'] : null,
            nossoNumero: isset($data['nossoNumero']) ? (string) $data['nossoNumero'] : null,
            dateCreated: isset($data['dateCreated']) ? (string) $data['dateCreated'] : null,
            confirmedDate: isset($data['confirmedDate']) ? (string) $data['confirmedDate'] : null,
            paymentDate: isset($data['paymentDate']) ? (string) $data['paymentDate'] : null,
            clientPaymentDate: isset($data['clientPaymentDate']) ? (string) $data['clientPaymentDate'] : null,
            installmentNumber: isset($data['installmentNumber']) ? (string) $data['installmentNumber'] : null,
            creditCardToken: isset($data['creditCardToken']) ? (string) $data['creditCardToken'] : null,
            creditCardNumber: isset($data['creditCardNumber']) ? (string) $data['creditCardNumber'] : null,
            creditCardBrand: isset($data['creditCardBrand']) ? (string) $data['creditCardBrand'] : null,
            object: isset($data['object']) ? (string) $data['object'] : null,
            deleted: (bool) ($data['deleted'] ?? false),
            postalService: (bool) ($data['postalService'] ?? false),
            anticipated: (bool) ($data['anticipated'] ?? false),
            anticipable: (bool) ($data['anticipable'] ?? false),
        );
    }
}
