<?php

namespace App\Tests\Unit\Entity;

use App\Entity\NewsComment;
use App\Entity\NewsPost;
use PHPUnit\Framework\TestCase;

class NewsCommentTest extends TestCase
{
    public function testConstructorSetsCreatedAtToNow(): void
    {
        $before = new \DateTimeImmutable();
        $comment = new NewsComment();
        $after = new \DateTimeImmutable();

        self::assertInstanceOf(\DateTimeImmutable::class, $comment->getCreatedAt());
        self::assertGreaterThanOrEqual($before, $comment->getCreatedAt());
        self::assertLessThanOrEqual($after, $comment->getCreatedAt());
    }

    public function testIdIsNullByDefault(): void
    {
        $comment = new NewsComment();
        self::assertNull($comment->getId());
    }

    public function testGettersAndSetters(): void
    {
        $comment = new NewsComment();

        $comment->setAuthor('Baptiste');
        self::assertSame('Baptiste', $comment->getAuthor());

        $comment->setContent('Great news article!');
        self::assertSame('Great news article!', $comment->getContent());

        $date = new \DateTimeImmutable('2025-01-15 10:00:00');
        $comment->setCreatedAt($date);
        self::assertSame($date, $comment->getCreatedAt());
    }

    public function testNewsIdRelation(): void
    {
        $comment = new NewsComment();
        $post = new NewsPost();

        self::assertNull($comment->getNewsId());

        $comment->setNewsId($post);
        self::assertSame($post, $comment->getNewsId());
    }

    public function testNewsIdCanBeSetToNull(): void
    {
        $comment = new NewsComment();
        $post = new NewsPost();

        $comment->setNewsId($post);
        $comment->setNewsId(null);

        self::assertNull($comment->getNewsId());
    }

    public function testSettersReturnStaticForFluentInterface(): void
    {
        $comment = new NewsComment();

        self::assertInstanceOf(NewsComment::class, $comment->setAuthor('Author'));
        self::assertInstanceOf(NewsComment::class, $comment->setContent('Content'));
        self::assertInstanceOf(NewsComment::class, $comment->setCreatedAt(new \DateTimeImmutable()));
        self::assertInstanceOf(NewsComment::class, $comment->setNewsId(null));
    }

    public function testEachNewCommentHasItsOwnCreatedAt(): void
    {
        $comment1 = new NewsComment();
        $comment2 = new NewsComment();

        // Both are separate instances, not the same reference
        self::assertNotSame($comment1->getCreatedAt(), $comment2->getCreatedAt());
    }
}
