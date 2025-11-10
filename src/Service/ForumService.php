<?php
namespace App\Service;

use App\Repository\ForumPostRepository;
use Symfony\Component\Routing\RouterInterface;


class ForumService
{
    public function __construct(public readonly ForumPostRepository $forumPostRepository, public readonly RouterInterface $router)
    {
    }

    public function generatePostArray(?int $id = null): array
    {
        $posts = [];

        if ($id !== null) {
            $post = $this->forumPostRepository->findOneById($id);
            if ($post !== null) {
                $posts = [$post];
            }
        } else {
            $posts = $this->forumPostRepository->findRootPosts();
        }

        $data = [];
        foreach ($posts as $post) {
            $childrenCount = $this->forumPostRepository->countChildren($post->getId());
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

}
