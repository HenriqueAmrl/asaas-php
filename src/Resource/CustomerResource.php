<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Resource;

use HenriqueAmrl\AsaasPhp\Dto\Customer;
use HenriqueAmrl\AsaasPhp\Dto\PageResult;

final class CustomerResource extends AbstractResource
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Customer
    {
        $response = $this->httpClient->post('/customers', $data);

        return Customer::fromArray($response);
    }

    public function find(string $id): Customer
    {
        $response = $this->httpClient->get('/customers/' . $id);

        return Customer::fromArray($response);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): Customer
    {
        $response = $this->httpClient->post('/customers/' . $id, $data);

        return Customer::fromArray($response);
    }

    public function delete(string $id): void
    {
        $this->httpClient->delete('/customers/' . $id);
    }

    /**
     * @param array<string, string|int|bool> $filters
     * @return PageResult<Customer>
     */
    public function list(array $filters = [], int $offset = 0, int $limit = 10): PageResult
    {
        $params = array_filter(array_merge($filters, ['offset' => $offset, 'limit' => $limit]));
        $response = $this->httpClient->get('/customers?' . http_build_query($params));

        /** @var array<int, array<string, mixed>> $rawItems */
        $rawItems = $response['data'] ?? [];

        return new PageResult(
            totalCount: (int) ($response['totalCount'] ?? 0),
            hasMore: (bool) ($response['hasMore'] ?? false),
            limit: (int) ($response['limit'] ?? $limit),
            offset: (int) ($response['offset'] ?? $offset),
            data: array_map(
                static fn (array $item): Customer => Customer::fromArray($item),
                $rawItems,
            ),
        );
    }
}
