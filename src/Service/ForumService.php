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
            $posts = $this->forumPostRepository->findAll();
        }
        
        foreach ($posts as $post) {
            $data[$post->getId()] = [
                'post' => $post,
                'href' => $this->router->generate('app_forum_details', ['id' => $post->getId()])
            ];
        }

        return $data;
    }

}