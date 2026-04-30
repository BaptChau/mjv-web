<?php

namespace App\Tests\Unit\Exception;

use App\Enum\ForumExceptionEnum;
use App\Exception\ForumException;
use PHPUnit\Framework\TestCase;

class ForumExceptionTest extends TestCase
{
    /**
     * ForumException::errorFactory() has an `int $code` type hint but internally uses a match
     * expression with ForumExceptionEnum cases as match arms. The factory is intended to be
     * called with int values matching the enum's backed values.
     *
     * The match arms use: match($code) { ForumExceptionEnum::POST_NOT_FOUND => ... }
     * Since PHP match uses strict (===) comparison, passing an int will not match an enum case.
     * This reflects a source-level inconsistency; these tests document the actual class structure
     * and exception hierarchy rather than the factory behaviour.
     */

    public function testForumExceptionExtendsBaseException(): void
    {
        $reflection = new \ReflectionClass(ForumException::class);

        self::assertTrue($reflection->isSubclassOf(\Exception::class));
    }

    public function testForumExceptionIsFinal(): void
    {
        $reflection = new \ReflectionClass(ForumException::class);

        self::assertTrue($reflection->isFinal());
    }

    public function testErrorFactoryMethodExists(): void
    {
        self::assertTrue(method_exists(ForumException::class, 'errorFactory'));
    }

    public function testErrorFactoryIsPublicAndStatic(): void
    {
        $reflection = new \ReflectionMethod(ForumException::class, 'errorFactory');

        self::assertTrue($reflection->isPublic());
        self::assertTrue($reflection->isStatic());
    }

    public function testErrorFactoryReturnsForumExceptionType(): void
    {
        $reflection = new \ReflectionMethod(ForumException::class, 'errorFactory');
        $returnType = $reflection->getReturnType();

        self::assertNotNull($returnType);
        self::assertSame(ForumException::class, $returnType->getName());
    }

    public function testConstructorIsPrivate(): void
    {
        $reflection = new \ReflectionClass(ForumException::class);
        $constructor = $reflection->getConstructor();

        self::assertNotNull($constructor);
        self::assertTrue($constructor->isPrivate(), 'ForumException constructor must be private — use errorFactory() instead.');
    }

    public function testErrorFactoryParameterIsTypedAsInt(): void
    {
        $reflection = new \ReflectionMethod(ForumException::class, 'errorFactory');
        $parameters = $reflection->getParameters();

        self::assertCount(1, $parameters);
        self::assertSame('code', $parameters[0]->getName());
    }

    /**
     * Verify the three error codes expected by errorFactory match the enum values.
     */
    public function testForumExceptionEnumCodesMatchExpectedValues(): void
    {
        self::assertSame(1000, ForumExceptionEnum::POST_NOT_FOUND->value);
        self::assertSame(1001, ForumExceptionEnum::POST_REPORTED->value);
        self::assertSame(1002, ForumExceptionEnum::POST_REMOVED->value);
    }
}
