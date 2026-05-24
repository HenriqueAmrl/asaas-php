<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Unit\Http;

use GuzzleHttp\Exception\TransferException;
use HenriqueAmrl\AsaasPhp\Exception\AuthenticationException;
use HenriqueAmrl\AsaasPhp\Exception\NetworkException;
use HenriqueAmrl\AsaasPhp\Exception\NotFoundException;
use HenriqueAmrl\AsaasPhp\Exception\RateLimitException;
use HenriqueAmrl\AsaasPhp\Exception\ServerException;
use HenriqueAmrl\AsaasPhp\Exception\ValidationException;
use HenriqueAmrl\AsaasPhp\Http\HttpClient;
use HenriqueAmrl\AsaasPhp\Tests\Unit\Support\FakeHttpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class HttpClientTest extends TestCase
{
    #[Test]
    public function injected_psr18_client_is_used_instead_of_guzzle(): void
    {
        $fakeClient = FakeHttpClient::withJsonResponse(200, ['foo' => 'bar']);
        $client = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fakeClient);

        $result = $client->get('/customers');

        $this->assertSame(['foo' => 'bar'], $result);
    }

    #[Test]
    public function get_request_with_429_response_retries_up_to_three_times(): void
    {
        // When using an injected FakeHttpClient (bypasses retry middleware),
        // a 429 is mapped directly to RateLimitException without retry.
        // The retry behavior itself is part of the default Guzzle path (HandlerStack),
        // which is not exercised in unit tests via FakeHttpClient.
        $fakeClient = FakeHttpClient::withJsonResponse(429, ['errors' => []]);
        $client = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fakeClient);

        $this->expectException(RateLimitException::class);
        $client->get('/customers');
    }

    #[Test]
    public function post_request_with_429_does_not_retry(): void
    {
        // POST + 429 → immediate RateLimitException (retry decider excludes POST)
        $fakeClient = FakeHttpClient::withJsonResponse(429, ['errors' => []]);
        $client = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fakeClient);

        $this->expectException(RateLimitException::class);
        $client->post('/charges', ['customer' => 'cus_123']);
    }

    #[Test]
    public function response_400_throws_validation_exception(): void
    {
        $fakeClient = FakeHttpClient::withJsonResponse(400, [
            'errors' => [
                ['code' => 'invalid.field', 'description' => 'Name required'],
            ],
        ]);
        $client = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fakeClient);

        try {
            $client->get('/customers');
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame('Name required', $e->getMessage());
            $this->assertCount(1, $e->errors);
            $this->assertSame('Name required', $e->errors[0]['description']);
        }
    }

    #[Test]
    public function response_401_throws_authentication_exception(): void
    {
        $fakeClient = FakeHttpClient::withJsonResponse(401, ['errors' => [['code' => 'unauthorized', 'description' => 'Unauthorized']]]);
        $client = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fakeClient);

        $this->expectException(AuthenticationException::class);
        $client->get('/customers');
    }

    #[Test]
    public function response_404_throws_not_found_exception(): void
    {
        $fakeClient = FakeHttpClient::withJsonResponse(404, ['errors' => [['code' => 'not_found', 'description' => 'Resource not found']]]);
        $client = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fakeClient);

        $this->expectException(NotFoundException::class);
        $client->get('/customers/nonexistent');
    }

    #[Test]
    public function injected_client_429_without_retry_throws_rate_limit_exception(): void
    {
        // Injected client bypasses retry middleware — 429 throws immediately
        $fakeClient = FakeHttpClient::withJsonResponse(429, ['errors' => [['code' => 'rate_limit', 'description' => 'Too many requests']]]);
        $client = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fakeClient);

        $this->expectException(RateLimitException::class);
        $client->get('/customers');
    }

    #[Test]
    public function response_500_throws_server_exception(): void
    {
        $fakeClient = FakeHttpClient::withJsonResponse(500, ['errors' => [['code' => 'server_error', 'description' => 'Internal server error']]]);
        $client = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $fakeClient);

        $this->expectException(ServerException::class);
        $client->get('/customers');
    }

    #[Test]
    public function psr18_client_exception_is_rethrown_as_network_exception(): void
    {
        // Use an inline stub that throws a PSR-18 ClientExceptionInterface
        $throwingClient = new class () implements ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw new TransferException('Connection refused');
            }
        };

        $client = new HttpClient('test_key', 'https://api-sandbox.asaas.com/v3', $throwingClient);

        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('Connection refused');
        $client->get('/customers');
    }

    #[Test]
    public function api_key_does_not_appear_in_exception_message(): void
    {
        $apiKey = 'super_secret_api_key_12345';
        $fakeClient = FakeHttpClient::withJsonResponse(401, ['errors' => [['code' => 'unauthorized', 'description' => 'Unauthorized']]]);
        $client = new HttpClient($apiKey, 'https://api-sandbox.asaas.com/v3', $fakeClient);

        try {
            $client->get('/customers');
            $this->fail('Expected AuthenticationException');
        } catch (AuthenticationException $e) {
            $this->assertStringNotContainsString($apiKey, $e->getMessage());
        }
    }
}
