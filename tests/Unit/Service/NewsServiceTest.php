<?php

namespace App\Tests\Unit\Service;

use App\Entity\NewsPost;
use App\Repository\NewsCommentRepository;
use App\Repository\NewsPostRepository;
use App\Service\NewsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NewsServiceTest extends TestCase
{
    private NewsPostRepository&MockObject $newsRepo;
    private NewsCommentRepository&MockObject $commentRepo;
    private NewsService $service;

    protected function setUp(): void
    {
        $this->newsRepo = $this->createMock(NewsPostRepository::class);
        $this->commentRepo = $this->createMock(NewsCommentRepository::class);
        $this->service = new NewsService($this->newsRepo, $this->commentRepo);
    }

    public function testGetLastNNewsDelegatesToRepository(): void
    {
        $posts = [new NewsPost(), new NewsPost()];
        $this->newsRepo->expects(self::once())
            ->method('findLatest')
            ->with(3)
            ->willReturn($posts);

        $result = $this->service->getLastNNews(3);
        self::assertSame($posts, $result);
    }

    public function testGetNewsByIdReturnsPostAndComments(): void
    {
        $post = new NewsPost();
        $post->setTitle('Test');

        $this->newsRepo->expects(self::once())
            ->method('findOneById')
            ->with(1)
            ->willReturn($post);

        $this->commentRepo->expects(self::once())
            ->method('findBy')
            ->willReturn([]);

        $result = $this->service->getNewsById(1);
        self::assertNotNull($result);
        self::assertSame($post, $result['post']);
        self::assertSame([], $result['comments']);
    }

    public function testGetNewsByIdReturnsNullWhenNotFound(): void
    {
        $this->newsRepo->expects(self::once())
            ->method('findOneById')
            ->with(999)
            ->willReturn(null);

        self::assertNull($this->service->getNewsById(999));
    }

    public function testGetPaginatedNews(): void
    {
        $posts = [new NewsPost()];
        $this->newsRepo->expects(self::once())
            ->method('findPaginated')
            ->with(1, 10)
            ->willReturn($posts);

        $this->newsRepo->expects(self::once())
            ->method('countAll')
            ->willReturn(25);

        $result = $this->service->getPaginatedNews(1, 10);
        self::assertSame($posts, $result['items']);
        self::assertSame(25, $result['total']);
        self::assertSame(3, $result['maxPage']);
    }

    public function testGetPaginatedNewsMinimumValues(): void
    {
        $this->newsRepo->method('findPaginated')->willReturn([]);
        $this->newsRepo->method('countAll')->willReturn(0);

        $result = $this->service->getPaginatedNews(-1, -5);
        self::assertSame(1, $result['maxPage']);
    }

    public function testGetPaginatedNewsNormalizesPageToOne(): void
    {
        $this->newsRepo->expects(self::once())
            ->method('findPaginated')
            ->with(1, 10)
            ->willReturn([]);
        $this->newsRepo->method('countAll')->willReturn(0);

        $this->service->getPaginatedNews(0, 10);
    }

    public function testGetPaginatedNewsNormalizesPerPageToOne(): void
    {
        $this->newsRepo->expects(self::once())
            ->method('findPaginated')
            ->with(1, 1)
            ->willReturn([]);
        $this->newsRepo->method('countAll')->willReturn(0);

        $this->service->getPaginatedNews(1, 0);
    }

    public function testGetPaginatedNewsMaxPageRoundsUp(): void
    {
        $this->newsRepo->method('findPaginated')->willReturn([]);
        $this->newsRepo->method('countAll')->willReturn(11);

        // 11 items / 10 per page = 1.1 -> ceil -> 2
        $result = $this->service->getPaginatedNews(1, 10);
        self::assertSame(2, $result['maxPage']);
    }

    public function testGetPaginatedNewsMaxPageIsAtLeastOne(): void
    {
        $this->newsRepo->method('findPaginated')->willReturn([]);
        $this->newsRepo->method('countAll')->willReturn(0);

        $result = $this->service->getPaginatedNews(1, 10);
        self::assertSame(1, $result['maxPage']);
    }

    public function testGetLastNNewsWithZeroReturnsEmpty(): void
    {
        $this->newsRepo->expects(self::once())
            ->method('findLatest')
            ->with(0)
            ->willReturn([]);

        $result = $this->service->getLastNNews(0);
        self::assertSame([], $result);
    }

    public function testGetNewsByIdCommentsAreSortedByDateDesc(): void
    {
        $post = new NewsPost();
        $post->setTitle('Test');

        $this->newsRepo->method('findOneById')->willReturn($post);

        $this->commentRepo->expects(self::once())
            ->method('findBy')
            ->with(['newsId' => null], ['createdAt' => 'DESC'])
            ->willReturn([]);

        $result = $this->service->getNewsById(1);
        self::assertNotNull($result);
    }

    public function testGetPaginatedNewsReturnsCorrectPageCount(): void
    {
        $this->newsRepo->method('findPaginated')->willReturn([]);
        $this->newsRepo->method('countAll')->willReturn(30);

        // 30 items / 10 per page = exactly 3 pages
        $result = $this->service->getPaginatedNews(1, 10);

        self::assertSame(3, $result['maxPage']);
        self::assertSame(30, $result['total']);
    }

    public function testGetNewsByIdReturnsCorrectComments(): void
    {
        $post = new NewsPost();
        $post->setTitle('Post with comments');

        $comment1 = new \App\Entity\NewsComment();
        $comment1->setAuthor('Alice');
        $comment2 = new \App\Entity\NewsComment();
        $comment2->setAuthor('Bob');

        $this->newsRepo->method('findOneById')->willReturn($post);
        $this->commentRepo->method('findBy')->willReturn([$comment1, $comment2]);

        $result = $this->service->getNewsById(1);

        self::assertCount(2, $result['comments']);
        self::assertSame($comment1, $result['comments'][0]);
        self::assertSame($comment2, $result['comments'][1]);
    }

    public function testGetLastNNewsWithLargeN(): void
    {
        $posts = array_fill(0, 20, new NewsPost());
        $this->newsRepo->expects(self::once())
            ->method('findLatest')
            ->with(20)
            ->willReturn($posts);

        $result = $this->service->getLastNNews(20);
        self::assertCount(20, $result);
    }
}
