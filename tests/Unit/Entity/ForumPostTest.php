<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ForumPost;
use PHPUnit\Framework\TestCase;

class ForumPostTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $post = new ForumPost();

        $post->setTitle('Forum title');
        self::assertSame('Forum title', $post->getTitle());

        $post->setAuthor('Author');
        self::assertSame('Author', $post->getAuthor());

        $post->setBody('Body text');
        self::assertSame('Body text', $post->getBody());

        $post->setParentId(42);
        self::assertSame(42, $post->getParentId());

        $post->setFlag(true);
        self::assertTrue($post->isFlag());
    }

    public function testNullableFields(): void
    {
        $post = new ForumPost();

        self::assertNull($post->getTitle());
        self::assertNull($post->getParentId());
        self::assertNull($post->isFlag());
    }

    public function testIdIsNullByDefault(): void
    {
        $post = new ForumPost();

        self::assertNull($post->getId());
    }

    public function testBodyIsNullByDefault(): void
    {
        $post = new ForumPost();

        self::assertNull($post->getBody());
    }

    public function testAuthorIsNullByDefault(): void
    {
        $post = new ForumPost();

        self::assertNull($post->getAuthor());
    }

    public function testTitleAcceptsNull(): void
    {
        $post = new ForumPost();
        $post->setTitle('Initial title');
        $post->setTitle(null);

        self::assertNull($post->getTitle());
    }

    public function testParentIdAcceptsNull(): void
    {
        $post = new ForumPost();
        $post->setParentId(5);
        $post->setParentId(null);

        self::assertNull($post->getParentId());
    }

    public function testSettersReturnStaticForFluentInterface(): void
    {
        $post = new ForumPost();

        self::assertInstanceOf(ForumPost::class, $post->setTitle('Title'));
        self::assertInstanceOf(ForumPost::class, $post->setAuthor('Author'));
        self::assertInstanceOf(ForumPost::class, $post->setBody('Body'));
        self::assertInstanceOf(ForumPost::class, $post->setParentId(1));
        self::assertInstanceOf(ForumPost::class, $post->setFlag(false));
    }
}
