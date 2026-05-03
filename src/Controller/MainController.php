<?php

namespace App\Controller;

use App\Service\ForumService;
use App\Service\NewsService;
use App\Service\TeamService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(
        ForumService $forumService,
        TeamService $teamService,
        NewsService $newsService,
    ): Response
    {
        $categories = $teamService->getAllWithCategoryLabel();

        $upcomingMatches = [
            [
                'date' => new \DateTimeImmutable('+3 days'),
                'opponent' => 'Lunéville',
                'teamLabel' => 'Seniors masculins',
                'location' => 'Domicile',
                'time' => '20h30',
            ],
            [
                'date' => new \DateTimeImmutable('+6 days'),
                'opponent' => 'Darnvilliers',
                'teamLabel' => 'Jeunes U18',
                'location' => 'Extérieur',
                'time' => '18h00',
            ],
        ];

        return $this->render(view: 'main/index.html.twig', parameters:[
            'lastPosts' => $forumService->getLastNPosts(5),
            'highlightedCategories' => array_slice($categories, 0, 3),
            'upcomingMatches' => $upcomingMatches,
            'latestNews' => $newsService->getLastNNews(2),
        ]);
    }

    #[Route('/mentions-legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('main/mentions-legales.html.twig');
    }

    #[Route('/politique-de-confidentialite', name: 'app_politique_confidentialite')]
    public function politiqueConfidentialite(): Response
    {
        return $this->render('main/politique-confidentialite.html.twig');
    }

    #[Route('/club', name: 'app_club')]
    public function club(TeamService $teamService): Response
    {
        $teams = $teamService->getAll();
        $staffHighlights = array_map(static function ($team) {
            return [
                'team' => $team->getLabel(),
                'coach' => $team->getCoach(),
                'photo' => $team->getPhotoPath(),
            ];
        }, array_slice($teams, 0, 2));

        return $this->render('main/club.html.twig', [
            'staffHighlights' => $staffHighlights,
            'values' => [
                [
                    'title' => 'Esprit collectif',
                    'description' => 'Nous mettons la solidarité et le respect au cœur de chaque entraînement et de chaque match.',
                ],
                [
                    'title' => 'Formation',
                    'description' => 'Des entraîneurs diplômés accompagnent chaque catégorie pour faire progresser les joueuses et joueurs.',
                ],
                [
                    'title' => 'Ouverture',
                    'description' => 'Le club accueille tous les profils, des plus jeunes aux seniors loisirs.',
                ],
            ],
        ]);
    }
}
