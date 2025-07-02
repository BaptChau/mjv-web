<?php
namespace App\Service;

use App\Repository\ForumPostRepository;
use Symfony\Component\Routing\RouterInterface;


class ForumService
{
    public function __construct(public readonly ForumPostRepository $forumPostRepository, public readonly RouterInterface $router)
    {
    }

    public function generatePostArray(?int $id = null):array
    {
        $data = [];

        if ($id !== null) {
            $posts = $this->forumPostRepository->findOneById($id);
        } else {
            $mainPosts = $this->forumPostRepository->findAll();
            $posts = $mainPosts;
        }
        
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
        $posts = $this->forumPostRepository->findAll();
        $lastPosts = array_slice($posts, 0, $n);
        return $lastPosts;
    }

}