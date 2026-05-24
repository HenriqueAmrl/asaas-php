<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Tests\Unit\Dto;

use HenriqueAmrl\AsaasPhp\Dto\PageResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PageResultTest extends TestCase
{
    #[Test]
    public function from_array_creates_page_result_with_matching_properties(): void
    {
        $raw = [
            'totalCount' => 5,
            'hasMore' => true,
            'limit' => 10,
            'offset' => 0,
            'data' => ['a', 'b'],
        ];

        $result = PageResult::fromArray($raw);

        $this->assertSame(5, $result->totalCount);
        $this->assertTrue($result->hasMore);
        $this->assertSame(10, $result->limit);
        $this->assertSame(0, $result->offset);
        $this->assertSame(['a', 'b'], $result->data);
    }

    #[Test]
    public function from_array_with_empty_array_uses_defaults(): void
    {
        $result = PageResult::fromArray([]);

        $this->assertSame(0, $result->totalCount);
        $this->assertFalse($result->hasMore);
        $this->assertSame(0, $result->limit);
        $this->assertSame(0, $result->offset);
        $this->assertSame([], $result->data);
    }

    #[Test]
    public function page_result_properties_are_readonly(): void
    {
        $result = PageResult::fromArray([
            'totalCount' => 5,
            'hasMore' => true,
            'limit' => 10,
            'offset' => 0,
            'data' => ['a', 'b'],
        ]);

        $threw = false;

        try {
            // @phpstan-ignore-next-line
            $result->totalCount = 99;
        } catch (\Error $e) {
            $threw = true;
        }

        $this->assertTrue($threw, 'Expected Error when assigning to readonly property totalCount');
    }
}
