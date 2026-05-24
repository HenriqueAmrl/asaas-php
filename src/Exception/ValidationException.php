<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Exception;

final class ValidationException extends AsaasException
{
    /**
     * @param array<int, array{code: string, description: string}> $errors
     */
    public function __construct(
        string $message,
        /** @var array<int, array{code: string, description: string}> */
        public readonly array $errors,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
