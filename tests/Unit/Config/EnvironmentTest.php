<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Unit\Config;

use HenriqueAmrl\AsaasPhp\Config\Environment;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EnvironmentTest extends TestCase
{
    #[Test]
    public function sandbox_base_url_returns_correct_value(): void
    {
        $this->assertSame('https://api-sandbox.asaas.com/v3', Environment::Sandbox->baseUrl());
    }

    #[Test]
    public function production_base_url_returns_correct_value(): void
    {
        $this->assertSame('https://api.asaas.com/v3', Environment::Production->baseUrl());
    }
}
