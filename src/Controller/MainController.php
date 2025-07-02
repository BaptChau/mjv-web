<?php

namespace App\Controller;

use App\Service\ForumService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(ForumService $forumService): Response
    {
        return $this->render(view: 'main/index.html.twig', parameters:[
            'lastPosts' => $forumService->getLastNPosts(5),
        ]);
    }
}
