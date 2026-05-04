<?php

namespace App\Controller;

use App\Repository\TeamMatchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CalendarController extends AbstractController
{
    #[Route('/calendrier', name: 'app_calendar')]
    public function index(Request $request, TeamMatchRepository $teamMatchRepository): Response
    {
        $selectedDateInput = $request->query->get('date');
        $selectedDate = null;
        if (is_string($selectedDateInput) && $selectedDateInput !== '') {
            $selectedDate = \DateTimeImmutable::createFromFormat('Y-m-d', $selectedDateInput) ?: null;
        }

        $selectedDateResults = [];
        if ($selectedDate instanceof \DateTimeImmutable) {
            $selectedDateResults = $teamMatchRepository->findByDate($selectedDate);
        }

        return $this->render('calendar/index.html.twig', [
            'upcomingMatches' => $teamMatchRepository->findUpcoming(),
            'lastWeekResults' => $teamMatchRepository->findLastWeekResults(),
            'selectedDateResults' => $selectedDateResults,
            'selectedDate' => $selectedDate,
        ]);
    }
}
