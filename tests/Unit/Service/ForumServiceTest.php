<?php

namespace App\Tests\Unit\Service;

use App\Entity\ForumPost;
use App\Repository\ForumPostRepository;
use App\Service\ForumService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class ForumServiceTest extends TestCase
{
    private ForumPostRepository&MockObject $repo;
    private RouterInterface&MockObject $router;
    private ForumService $service;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(ForumPostRepository::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->service = new ForumService($this->repo, $this->router);
    }

    public function testGetLastNPostsDelegatesToRepository(): void
    {
        $posts = [new ForumPost()];
        $this->repo->expects(self::once())
            ->method('findLatestRootPosts')
            ->with(5)
            ->willReturn($posts);

        self::assertSame($posts, $this->service->getLastNPosts(5));
    }

    public function testCountAnswersTreeCountsRecursively(): void
    {
        $tree = [
            [
                'post' => new ForumPost(),
                'children' => [
                    [
                        'post' => new ForumPost(),
                        'children' => [
                            ['post' => new ForumPost(), 'children' => []],
                        ],
                    ],
                ],
            ],
            [
                'post' => new ForumPost(),
                'children' => [],
            ],
        ];

        self::assertSame(4, $this->service->countAnswersTree($tree));
    }

    public function testCountAnswersTreeEmptyTree(): void
    {
        self::assertSame(0, $this->service->countAnswersTree([]));
    }

    public function testGeneratePostArrayWithId(): void
    {
        $post = $this->createMock(ForumPost::class);
        $post->method('getId')->willReturn(1);

        $this->repo->expects(self::once())
            ->method('findOneById')
            ->with(1)
            ->willReturn($post);

        $this->repo->expects(self::once())
            ->method('countChildrenForParents')
            ->with([1])
            ->willReturn([1 => 3]);

        $this->router->expects(self::once())
            ->method('generate')
            ->with('app_forum_details', ['id' => 1])
            ->willReturn('/forum/post/1');

        $result = $this->service->generatePostArray(1);
        self::assertCount(1, $result);
        self::assertSame($post, $result[1]['post']);
        self::assertSame('/forum/post/1', $result[1]['href']);
        self::assertSame(3, $result[1]['children_count']);
    }

    public function testGeneratePostArrayWithNullIdReturnsNotFound(): void
    {
        $this->repo->expects(self::once())
            ->method('findRootPosts')
            ->willReturn([]);

        $this->repo->expects(self::once())
            ->method('countChildrenForParents')
            ->with([])
            ->willReturn([]);

        $result = $this->service->generatePostArray();
        self::assertCount(0, $result);
    }

    public function testGeneratePostArrayWithLimitAndOffset(): void
    {
        $post = $this->createMock(ForumPost::class);
        $post->method('getId')->willReturn(10);

        $this->repo->expects(self::once())
            ->method('findRootPosts')
            ->with(5, 10)
            ->willReturn([$post]);

        $this->repo->method('countChildrenForParents')->willReturn([]);

        $this->router->method('generate')
            ->with('app_forum_details', ['id' => 10])
            ->willReturn('/forum/post/10');

        $result = $this->service->generatePostArray(null, 5, 10);

        self::assertArrayHasKey(10, $result);
    }

    public function testGeneratePostArrayPostWithNoChildrenHasZeroCount(): void
    {
        $post = $this->createMock(ForumPost::class);
        $post->method('getId')->willReturn(7);

        $this->repo->method('findOneById')->willReturn($post);
        // countChildrenForParents returns no entry for this post id
        $this->repo->method('countChildrenForParents')->with([7])->willReturn([]);
        $this->router->method('generate')->willReturn('/forum/post/7');

        $result = $this->service->generatePostArray(7);

        self::assertSame(0, $result[7]['children_count']);
    }

    public function testGeneratePostArrayReturnsEmptyWhenPostNotFound(): void
    {
        $this->repo->expects(self::once())
            ->method('findOneById')
            ->with(404)
            ->willReturn(null);

        $this->repo->expects(self::once())
            ->method('countChildrenForParents')
            ->with([])
            ->willReturn([]);

        $result = $this->service->generatePostArray(404);

        self::assertCount(0, $result);
    }

    public function testGetAnswersTreeBuildsRecursiveTree(): void
    {
        $parent = $this->createMock(ForumPost::class);
        $parent->method('getId')->willReturn(1);

        $child1 = $this->createMock(ForumPost::class);
        $child1->method('getId')->willReturn(2);

        $child2 = $this->createMock(ForumPost::class);
        $child2->method('getId')->willReturn(3);

        $grandchild = $this->createMock(ForumPost::class);
        $grandchild->method('getId')->willReturn(4);

        // findChildren(1) => [$child1, $child2]
        // findChildren(2) => [$grandchild]
        // findChildren(3) => []
        // findChildren(4) => []
        $this->repo->expects(self::exactly(4))
            ->method('findChildren')
            ->willReturnMap([
                [1, [$child1, $child2]],
                [2, [$grandchild]],
                [3, []],
                [4, []],
            ]);

        $tree = $this->service->getAnswersTree(1);

        self::assertCount(2, $tree);
        self::assertSame($child1, $tree[0]['post']);
        self::assertCount(1, $tree[0]['children']);
        self::assertSame($grandchild, $tree[0]['children'][0]['post']);
        self::assertCount(0, $tree[0]['children'][0]['children']);
        self::assertSame($child2, $tree[1]['post']);
        self::assertCount(0, $tree[1]['children']);
    }

    public function testGetAnswersTreeReturnsEmptyForLeafNode(): void
    {
        $this->repo->expects(self::once())
            ->method('findChildren')
            ->with(99)
            ->willReturn([]);

        $tree = $this->service->getAnswersTree(99);

        self::assertSame([], $tree);
    }

    public function testGeneratePostArrayWithZeroLimitPassesNullToRepository(): void
    {
        // limit = 0 should be treated as "no limit" (null passed to findRootPosts)
        $post = $this->createMock(ForumPost::class);
        $post->method('getId')->willReturn(5);

        $this->repo->expects(self::once())
            ->method('findRootPosts')
            ->with(null, 0)
            ->willReturn([$post]);

        $this->repo->method('countChildrenForParents')->willReturn([]);
        $this->router->method('generate')->willReturn('/forum/post/5');

        $result = $this->service->generatePostArray(null, 0, 0);

        self::assertArrayHasKey(5, $result);
    }

    public function testGeneratePostArrayMultiplePostsWithMixedChildrenCounts(): void
    {
        $post1 = $this->createMock(ForumPost::class);
        $post1->method('getId')->willReturn(10);

        $post2 = $this->createMock(ForumPost::class);
        $post2->method('getId')->willReturn(20);

        $this->repo->method('findRootPosts')->willReturn([$post1, $post2]);
        $this->repo->method('countChildrenForParents')
            ->with([10, 20])
            ->willReturn([10 => 5, 20 => 0]);

        $this->router->method('generate')
            ->willReturnCallback(function (string $route, array $params) {
                return '/forum/post/' . $params['id'];
            });

        $result = $this->service->generatePostArray();

        self::assertSame(5, $result[10]['children_count']);
        self::assertSame(0, $result[20]['children_count']);
    }

    public function testGetLastNPostsWithZero(): void
    {
        $this->repo->expects(self::once())
            ->method('findLatestRootPosts')
            ->with(0)
            ->willReturn([]);

        self::assertSame([], $this->service->getLastNPosts(0));
    }
}
