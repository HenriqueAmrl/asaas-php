<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Dto;

final class PixQrCode
{
    public function __construct(
        public readonly string $encodedImage,
        public readonly string $payload,
        public readonly string $expirationDate,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            encodedImage: (string) ($data['encodedImage'] ?? ''),
            payload: (string) ($data['payload'] ?? ''),
            expirationDate: (string) ($data['expirationDate'] ?? ''),
        );
    }
}
