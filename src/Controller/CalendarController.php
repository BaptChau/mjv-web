<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CalendarController extends AbstractController
{
    #[Route('/calendrier', name: 'app_calendar')]
    public function index(Request $request): Response
    {
        $upcomingMatches = [
            [
                'date' => new \DateTimeImmutable('+2 days'),
                'opponent' => 'Bar-le-Duc',
                'teamLabel' => 'Seniors féminines',
                'location' => 'Domicile',
                'time' => '19h30',
                'competition' => 'Championnat',
            ],
            [
                'date' => new \DateTimeImmutable('+4 days'),
                'opponent' => 'Commercy',
                'teamLabel' => 'U18',
                'location' => 'Extérieur',
                'time' => '16h00',
                'competition' => 'Coupe régionale',
            ],
            [
                'date' => new \DateTimeImmutable('+7 days'),
                'opponent' => 'Verdun',
                'teamLabel' => 'Seniors masculins',
                'location' => 'Domicile',
                'time' => '20h30',
                'competition' => 'Championnat',
            ],
        ];

        $results = [
            [
                'date' => new \DateTimeImmutable('-1 day'),
                'opponent' => 'Ligny',
                'teamLabel' => 'Seniors féminines',
                'location' => 'Extérieur',
                'score' => '22 - 26',
                'competition' => 'Championnat',
            ],
            [
                'date' => new \DateTimeImmutable('-2 days'),
                'opponent' => 'Saint-Mihiel',
                'teamLabel' => 'U18',
                'location' => 'Domicile',
                'score' => '31 - 19',
                'competition' => 'Coupe régionale',
            ],
            [
                'date' => new \DateTimeImmutable('-5 days'),
                'opponent' => 'Commercy',
                'teamLabel' => 'Seniors masculins',
                'location' => 'Extérieur',
                'score' => '27 - 27',
                'competition' => 'Championnat',
            ],
            [
                'date' => new \DateTimeImmutable('-10 days'),
                'opponent' => 'Toul',
                'teamLabel' => 'U15',
                'location' => 'Domicile',
                'score' => '18 - 20',
                'competition' => 'Championnat',
            ],
        ];

        $selectedDateInput = $request->query->get('date');
        $selectedDate = null;
        if (is_string($selectedDateInput) && $selectedDateInput !== '') {
            $selectedDate = \DateTimeImmutable::createFromFormat('Y-m-d', $selectedDateInput) ?: null;
        }

        $now = new \DateTimeImmutable('today');
        $lastWeekStart = $now->modify('-7 days');
        $lastWeekResults = array_values(array_filter($results, static function (array $match) use ($lastWeekStart, $now): bool {
            return $match['date'] >= $lastWeekStart && $match['date'] <= $now;
        }));

        $selectedDateResults = [];
        if ($selectedDate instanceof \DateTimeImmutable) {
            $selectedDateResults = array_values(array_filter($results, static function (array $match) use ($selectedDate): bool {
                return $match['date']->format('Y-m-d') === $selectedDate->format('Y-m-d');
            }));
        }

        return $this->render('calendar/index.html.twig', [
            'upcomingMatches' => $upcomingMatches,
            'lastWeekResults' => $lastWeekResults,
            'selectedDateResults' => $selectedDateResults,
            'selectedDate' => $selectedDate,
        ]);
    }
}
