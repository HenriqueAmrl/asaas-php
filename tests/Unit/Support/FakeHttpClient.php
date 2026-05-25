<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Unit\Support;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class FakeHttpClient implements ClientInterface
{
    /** @var array<int, ResponseInterface> */
    private array $responses;

    private ?RequestInterface $lastRequest = null;

    /** @param array<int, ResponseInterface> $responses */
    public function __construct(array $responses = [])
    {
        $this->responses = $responses;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if ($this->responses === []) {
            throw new \RuntimeException('FakeHttpClient: no more responses queued');
        }

        $this->lastRequest = $request;

        return array_shift($this->responses);
    }

    public function getLastRequest(): RequestInterface
    {
        if ($this->lastRequest === null) {
            throw new \RuntimeException('FakeHttpClient: no request has been sent yet');
        }

        return $this->lastRequest;
    }

    /** @return array<string, mixed> */
    public function getLastRequestBody(): array
    {
        $body = (string) $this->getLastRequest()->getBody();

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }

    /** @param array<string, mixed> $body */
    public static function withJsonResponse(int $status, array $body): self
    {
        return new self([
            new Response($status, ['Content-Type' => 'application/json'], json_encode($body, JSON_THROW_ON_ERROR)),
        ]);
    }
}
