<?php

namespace App\Controller;

use App\Enum\CategoryEnum;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class TeamController extends AbstractController
{
    #[Route('/equipes', name: 'app_team_index')]
    public function index(TeamRepository $teamRepository): Response
    {
        $teams = $teamRepository->findAll();
        return $this->render('team/index.html.twig', [
            'teams' => $teams,
        ]);
    }

    #[Route('/equipes/{slug}', name: 'app_team_details', requirements: ['slug' => '[a-z0-9\-]+'])]
    public function details(string $slug, TeamRepository $teamRepository, CategoryEnum $categoryEnum): Response
    {
        $team = $teamRepository->findOneBy(['category' => $categoryEnum::fromLabel($slug)?->value]);

        if (!$team) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }

        return $this->render('team/details.html.twig', [
            'team' => $team,
        ]);
    }
}
