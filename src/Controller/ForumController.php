<?php

namespace App\Controller;

use App\Exception\Enum\ForumExceptionEnum;
use App\Exception\ForumException;
use App\Repository\ForumPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

final class ForumController extends AbstractController
{
    #[Route('/forum', name: 'app_forum', methods: Request::METHOD_GET)]
    public function index(ForumPostRepository $forumPostRepository): Response
    {
        $posts = $forumPostRepository->findAll();

        return $this->render(
            view: 'forum/index.html.twig',
            parameters: [
                'posts' => $posts,
            ]
        );
    }

    #[Route('/forum/post/{id}', name:'app_forum_details', methods: Request::METHOD_GET)]
    public function getPostById(int $id, ForumPostRepository $forumPostRepository): Response
    {
        $post = $forumPostRepository->findOneById($id);
        if ($post == null) {
            return $this->render(
                view: 'forum/error.html.twig',
            );
        }
        return $this->render(
            view: 'forum/details.html.twig',
            parameters: [
                'post' => $post
            ]
            );
    }
}
