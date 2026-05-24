<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\RetryMiddleware;
use GuzzleHttp\Utils;
use HenriqueAmrl\AsaasPhp\Exception\AsaasException;
use HenriqueAmrl\AsaasPhp\Exception\AuthenticationException;
use HenriqueAmrl\AsaasPhp\Exception\AuthorizationException;
use HenriqueAmrl\AsaasPhp\Exception\NetworkException;
use HenriqueAmrl\AsaasPhp\Exception\NotFoundException;
use HenriqueAmrl\AsaasPhp\Exception\RateLimitException;
use HenriqueAmrl\AsaasPhp\Exception\ServerException;
use HenriqueAmrl\AsaasPhp\Exception\ValidationException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class HttpClient
{
    private ClientInterface $client;

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        ?ClientInterface $client = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ) {
        $defaultFactory = new HttpFactory();
        $this->requestFactory = $requestFactory ?? $defaultFactory;
        $this->streamFactory = $streamFactory ?? $defaultFactory;
        $this->client = $client ?? $this->buildDefaultGuzzleClient();
    }

    private function buildDefaultGuzzleClient(): GuzzleClient
    {
        $decider = static function (
            int $retries,
            RequestInterface $request,
            ?ResponseInterface $response,
            ?\Throwable $exception,
        ): bool {
            if ($retries >= 3) {
                return false;
            }

            $isIdempotent = in_array(strtoupper($request->getMethod()), ['GET', 'DELETE'], true);
            if (!$isIdempotent) {
                return false;
            }

            if ($response !== null) {
                $status = $response->getStatusCode();

                return $status === 429 || in_array($status, [502, 503, 504], true);
            }

            return false;
        };

        $stack = new HandlerStack(Utils::chooseHandler());
        $stack->push(Middleware::prepareBody(), 'prepare_body');
        $stack->push(Middleware::retry($decider, RetryMiddleware::exponentialDelay(...)), 'retry');
        // httpErrors intentionally excluded - handleResponse() maps status codes manually

        return new GuzzleClient(['handler' => $stack]);
    }

    /** @return array<string, mixed> */
    public function get(string $path): array
    {
        $request = $this->requestFactory->createRequest('GET', $this->baseUrl.$path)
            ->withHeader('access_token', $this->apiKey)
            ->withHeader('Accept', 'application/json');

        return $this->send($request);
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    public function post(string $path, array $body = []): array
    {
        $request = $this->requestFactory->createRequest('POST', $this->baseUrl.$path)
            ->withHeader('access_token', $this->apiKey)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($this->streamFactory->createStream(
                json_encode($body, JSON_THROW_ON_ERROR)
            ));

        return $this->send($request);
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    public function put(string $path, array $body = []): array
    {
        $request = $this->requestFactory->createRequest('PUT', $this->baseUrl.$path)
            ->withHeader('access_token', $this->apiKey)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($this->streamFactory->createStream(
                json_encode($body, JSON_THROW_ON_ERROR)
            ));

        return $this->send($request);
    }

    /** @return array<string, mixed> */
    public function delete(string $path): array
    {
        $request = $this->requestFactory->createRequest('DELETE', $this->baseUrl.$path)
            ->withHeader('access_token', $this->apiKey)
            ->withHeader('Accept', 'application/json');

        return $this->send($request);
    }

    /** @return array<string, mixed> */
    private function send(RequestInterface $request): array
    {
        try {
            $response = $this->client->sendRequest($request);
        } catch (\Psr\Http\Client\ClientExceptionInterface $e) {
            throw new NetworkException($e->getMessage(), 0, $e);
        }

        return $this->handleResponse($response);
    }

    /** @return array<string, mixed> */
    private function handleResponse(ResponseInterface $response): array
    {
        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status >= 200 && $status < 300) {
            // 204 No Content and similar return an empty body - treat as empty array
            if ($body === '') {
                return [];
            }

            try {
                /** @var array<string, mixed> $data */
                $data = json_decode($body, associative: true, flags: JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new NetworkException('Invalid JSON in response body: ' . $e->getMessage(), 0, $e);
            }

            return $data;
        }

        throw $this->mapHttpError($status, $body);
    }

    private function mapHttpError(int $status, string $body): AsaasException
    {
        try {
            $decoded = json_decode($body, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $decoded = null;
        }

        /** @var array{errors?: array<int, array{code: string, description: string}>} $data */
        $data = is_array($decoded) ? $decoded : [];
        $errors = $data['errors'] ?? [];
        $firstMessage = $errors[0]['description'] ?? 'Unknown error';

        return match (true) {
            $status === 400 => new ValidationException($firstMessage, $errors, $status),
            $status === 401 => new AuthenticationException($firstMessage, $status),
            $status === 403 => new AuthorizationException($firstMessage, $status),
            $status === 404 => new NotFoundException($firstMessage, $status),
            $status === 429 => new RateLimitException($firstMessage, $status),
            $status >= 500  => new ServerException($firstMessage, $status),
            default         => new NetworkException($firstMessage, $status),
        };
    }
}
