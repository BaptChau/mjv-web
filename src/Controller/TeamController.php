<?php

namespace App\Controller;

use App\Service\TeamService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class TeamController extends AbstractController
{
    #[Route('/equipes', name: 'app_team_index')]
    public function index(TeamService $teamService): Response
    {
        $teams = $teamService->getAllWithCategoryLabel();

        return $this->render('team/index.html.twig', [
            'teams' => $teams,
        ]);
    }

    #[Route('/equipes/{slug}', name: 'app_team_details', requirements: ['slug' => '[a-z0-9\-]+'])]
    public function details(string $slug, TeamService $teamService): Response
    {
        $teams = $teamService->findAllByCategorySlug($slug);
        if (count($teams) === 0) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }

        $categoryValue = $teams[0]->getCategory();
        return $this->render('team/details.html.twig', [
            'teams' => $teams,
            'categoryLabel' => $teamService->getCategoryLabel($categoryValue),
            'categorySlug' => $teamService->getCategorySlug($categoryValue),
        ]);
    }

    #[Route('/equipes/{slug}/{id}', name: 'app_team_show', requirements: ['slug' => '[a-z0-9\-]+', 'id' => '\d+'])]
    public function show(string $slug, int $id, TeamService $teamService): Response
    {
        $team = $teamService->findOne($id);
        if (!$team || !$teamService->ensureTeamMatchesSlug($team, $slug)) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }

        $categorySlug = $teamService->getCategorySlug($team->getCategory());

        return $this->render('team/show.html.twig', [
            'team' => $team,
            'categoryLabel' => $teamService->getCategoryLabel($team->getCategory()),
            'categorySlug' => $categorySlug,
        ]);
    }
}
