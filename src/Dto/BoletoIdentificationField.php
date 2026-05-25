<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Dto;

final class BoletoIdentificationField
{
    public function __construct(
        public readonly string $identificationField,
        public readonly string $nossoNumero,
        public readonly string $barCode,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            identificationField: (string) ($data['identificationField'] ?? ''),
            nossoNumero: (string) ($data['nossoNumero'] ?? ''),
            barCode: (string) ($data['barCode'] ?? ''),
        );
    }
}
