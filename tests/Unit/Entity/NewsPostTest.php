<?php

namespace App\Tests\Unit\Entity;

use App\Entity\NewsComment;
use App\Entity\NewsPost;
use PHPUnit\Framework\TestCase;

class NewsPostTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $post = new NewsPost();

        self::assertInstanceOf(\DateTimeImmutable::class, $post->getCreatedAt());
        self::assertCount(0, $post->getNewsComments());
    }

    public function testGettersAndSetters(): void
    {
        $post = new NewsPost();

        $post->setTitle('Test');
        self::assertSame('Test', $post->getTitle());

        $post->setAuthor('Author');
        self::assertSame('Author', $post->getAuthor());

        $post->setContent('Content');
        self::assertSame('Content', $post->getContent());

        $post->setImgPath('/img/test.jpg');
        self::assertSame('/img/test.jpg', $post->getImgPath());

        $date = new \DateTimeImmutable();
        $post->setUpdateAt($date);
        self::assertSame($date, $post->getUpdateAt());
    }

    public function testToString(): void
    {
        $post = new NewsPost();
        self::assertSame('', (string) $post);

        $post->setTitle('Mon titre');
        self::assertSame('Mon titre', (string) $post);
    }

    public function testAddRemoveNewsComment(): void
    {
        $post = new NewsPost();
        $comment = new NewsComment();

        $post->addNewsComment($comment);
        self::assertCount(1, $post->getNewsComments());
        self::assertSame($post, $comment->getNewsId());

        // Adding same comment again should not duplicate
        $post->addNewsComment($comment);
        self::assertCount(1, $post->getNewsComments());

        $post->removeNewsComment($comment);
        self::assertCount(0, $post->getNewsComments());
        self::assertNull($comment->getNewsId());
    }

    public function testSetCreatedAt(): void
    {
        $post = new NewsPost();
        $date = new \DateTimeImmutable('2024-06-01 10:00:00');

        $result = $post->setCreatedAt($date);

        self::assertSame($date, $post->getCreatedAt());
        self::assertInstanceOf(NewsPost::class, $result);
    }

    public function testIdIsNullByDefault(): void
    {
        $post = new NewsPost();

        self::assertNull($post->getId());
    }

    public function testImgPathIsNullByDefault(): void
    {
        $post = new NewsPost();

        self::assertNull($post->getImgPath());
    }

    public function testImgPathCanBeSetToNull(): void
    {
        $post = new NewsPost();
        $post->setImgPath('/img/test.jpg');
        $post->setImgPath(null);

        self::assertNull($post->getImgPath());
    }

    public function testSettersReturnStaticForFluentInterface(): void
    {
        $post = new NewsPost();
        $date = new \DateTimeImmutable();

        self::assertInstanceOf(NewsPost::class, $post->setTitle('Title'));
        self::assertInstanceOf(NewsPost::class, $post->setAuthor('Author'));
        self::assertInstanceOf(NewsPost::class, $post->setContent('Content'));
        self::assertInstanceOf(NewsPost::class, $post->setCreatedAt($date));
        self::assertInstanceOf(NewsPost::class, $post->setUpdateAt($date));
        self::assertInstanceOf(NewsPost::class, $post->setImgPath('/img.jpg'));
    }

    public function testRemoveCommentThatBelongsToAnotherPostDoesNotClearNewsId(): void
    {
        $post1 = new NewsPost();
        $post2 = new NewsPost();
        $comment = new NewsComment();

        // Manually set the newsId to post2 without adding through post1
        $comment->setNewsId($post2);

        // Simulating the condition: comment is in post1's collection but newsId points elsewhere
        // We use reflection to inject it to test the guard condition in removeNewsComment
        $reflection = new \ReflectionProperty(NewsPost::class, 'newsComments');
        $reflection->setAccessible(true);
        $collection = $reflection->getValue($post1);
        $collection->add($comment);

        // Now remove — since $comment->getNewsId() === $post2, not $post1, it should NOT null the newsId
        $post1->removeNewsComment($comment);

        self::assertSame($post2, $comment->getNewsId(), 'newsId should not be nulled when it already points to another post.');
    }
}
