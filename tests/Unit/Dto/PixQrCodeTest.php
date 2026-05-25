<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Unit\Dto;

use HenriqueAmrl\AsaasPhp\Dto\PixQrCode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PixQrCodeTest extends TestCase
{
    #[Test]
    public function fromArray_returns_typed_dto_with_all_three_fields_populated(): void
    {
        $dto = PixQrCode::fromArray([
            'encodedImage' => 'iVBORw0KGgoAAAANSUhEUgAA',
            'payload' => '00020126580014BR.GOV.BCB.PIX',
            'expirationDate' => '2027-06-15',
        ]);

        $this->assertInstanceOf(PixQrCode::class, $dto);
        $this->assertSame('iVBORw0KGgoAAAANSUhEUgAA', $dto->encodedImage);
        $this->assertSame('00020126580014BR.GOV.BCB.PIX', $dto->payload);
        $this->assertSame('2027-06-15', $dto->expirationDate);
    }

    #[Test]
    public function fromArray_with_missing_keys_returns_empty_strings(): void
    {
        $dto = PixQrCode::fromArray([]);

        $this->assertSame('', $dto->encodedImage);
        $this->assertSame('', $dto->payload);
        $this->assertSame('', $dto->expirationDate);
    }
}
