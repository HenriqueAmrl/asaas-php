<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Resource;

use HenriqueAmrl\AsaasPhp\Http\HttpClient;

abstract class AbstractResource
{
    public function __construct(
        protected readonly HttpClient $httpClient,
    ) {
    }
}
