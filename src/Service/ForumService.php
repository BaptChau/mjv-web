<?php
namespace App\Service;

use App\Repository\ForumPostRepository;
use Symfony\Component\Routing\RouterInterface;


class ForumService
{
    public function __construct(public readonly ForumPostRepository $forumPostRepository, public readonly RouterInterface $router)
    {
    }

    public function generatePostArray(?int $id = null, int $limit = 0, int $offset = 0): array
    {
        $posts = [];

        if ($id !== null) {
            $post = $this->forumPostRepository->findOneById($id);
            if ($post !== null) {
                $posts = [$post];
            }
        } else {
            $posts = $this->forumPostRepository->findRootPosts(
                limit: $limit > 0 ? $limit : null,
                offset: $offset
            );
        }

        $postIds = [];
        foreach ($posts as $post) {
            $postIds[] = $post->getId();
        }
        $childrenCounts = $this->forumPostRepository->countChildrenForParents($postIds);

        $data = [];
        foreach ($posts as $post) {
            $postId = $post->getId();
            $childrenCount = $childrenCounts[$postId] ?? 0;
            $data[$post->getId()] = [
                'post' => $post,
                'href' => $this->router->generate('app_forum_details', ['id' => $post->getId()]),
                'children_count' => $childrenCount,
            ];
        }

        return $data;
    }

    public function getLastNPosts(int $n): array
    {
        return $this->forumPostRepository->findLatestRootPosts($n);
    }

    public function getAnswersTree(int $postId): array
    {
        return $this->buildAnswersTree($postId);
    }

    private function buildAnswersTree(int $parentId): array
    {
        $children = $this->forumPostRepository->findChildren($parentId);

        $tree = [];

        foreach ($children as $child) {
            $tree[] = [
                'post' => $child,
                'children' => $this->buildAnswersTree($child->getId()),
            ];
        }

        return $tree;
    }

    public function countAnswersTree(array $tree): int
    {
        $count = 0;

        foreach ($tree as $node) {
            $count++;
            if (!empty($node['children'])) {
                $count += $this->countAnswersTree($node['children']);
            }
        }

        return $count;
    }

}
