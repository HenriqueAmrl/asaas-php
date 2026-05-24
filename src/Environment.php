<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp;

enum Environment
{
    case Sandbox;
    case Production;

    public function baseUrl(): string
    {
        return match ($this) {
            Environment::Sandbox    => 'https://api-sandbox.asaas.com/v3',
            Environment::Production => 'https://api.asaas.com/v3',
        };
    }
}
