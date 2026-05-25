<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Unit\Dto;

use HenriqueAmrl\AsaasPhp\Dto\BoletoIdentificationField;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BoletoIdentificationFieldTest extends TestCase
{
    #[Test]
    public function fromArray_returns_typed_dto_with_all_three_fields_populated(): void
    {
        $dto = BoletoIdentificationField::fromArray([
            'identificationField' => '12345',
            'nossoNumero' => '67890',
            'barCode' => 'ABCDE',
        ]);

        $this->assertInstanceOf(BoletoIdentificationField::class, $dto);
        $this->assertSame('12345', $dto->identificationField);
        $this->assertSame('67890', $dto->nossoNumero);
        $this->assertSame('ABCDE', $dto->barCode);
    }
}
