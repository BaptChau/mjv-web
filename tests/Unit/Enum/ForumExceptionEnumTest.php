<?php

namespace App\Tests\Unit\Enum;

use App\Enum\ForumExceptionEnum;
use PHPUnit\Framework\TestCase;

class ForumExceptionEnumTest extends TestCase
{
    public function testPostNotFoundHasCorrectValue(): void
    {
        self::assertSame(1000, ForumExceptionEnum::POST_NOT_FOUND->value);
    }

    public function testPostReportedHasCorrectValue(): void
    {
        self::assertSame(1001, ForumExceptionEnum::POST_REPORTED->value);
    }

    public function testPostRemovedHasCorrectValue(): void
    {
        self::assertSame(1002, ForumExceptionEnum::POST_REMOVED->value);
    }

    public function testAllCasesAreBackedByInt(): void
    {
        foreach (ForumExceptionEnum::cases() as $case) {
            self::assertIsInt($case->value);
        }
    }

    public function testTryFromReturnsEnumForValidValue(): void
    {
        self::assertSame(ForumExceptionEnum::POST_NOT_FOUND, ForumExceptionEnum::tryFrom(1000));
        self::assertSame(ForumExceptionEnum::POST_REPORTED, ForumExceptionEnum::tryFrom(1001));
        self::assertSame(ForumExceptionEnum::POST_REMOVED, ForumExceptionEnum::tryFrom(1002));
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        self::assertNull(ForumExceptionEnum::tryFrom(9999));
    }

    public function testEnumHasExactlyThreeCases(): void
    {
        self::assertCount(3, ForumExceptionEnum::cases());
    }

    public function testAllValuesAreDistinct(): void
    {
        $values = array_map(fn (ForumExceptionEnum $case) => $case->value, ForumExceptionEnum::cases());

        self::assertSame(count($values), count(array_unique($values)), 'All ForumExceptionEnum values must be unique.');
    }
}
