<?php

namespace App\Service;

use App\Repository\ForumPostRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class BreadcrumbsService
{

    public function __construct(public RequestStack $requestStack, public  RouterInterface $router, public readonly ForumPostRepository $forumPostRepository)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    public function getBreadcrumbs(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $route = $request?->attributes->get('_route');
        $breadcrumbs = [];

        // Always add home
        $breadcrumbs[] = [
            'label' => 'Accueil',
            'url' => $this->router->generate('app_main'),
        ];

        if ($route === 'app_forum' || $route === 'app_forum_details') {
            $breadcrumbs[] = [
                'label' => 'Forum',
                'url' => $this->router->generate('app_forum'),
            ];
        }

        if ($route === 'app_forum_details') {
            $postId = $request->attributes->get('id');
            $post = $this->forumPostRepository->findOneById($postId);
            if ($post && method_exists($post, 'getTitle')) {
                $breadcrumbs[] = [
                    'label' => $post->getTitle(),
                    'url' => null,
                ];
            }
        }

        return $breadcrumbs;
    }
}