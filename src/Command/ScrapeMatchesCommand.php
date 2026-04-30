<?php

namespace App\Command;

use App\Entity\TeamMatch;
use App\Repository\TeamMatchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:scrape-matches',
    description: 'Scrape match data from ffhandball.fr for all teams with a championship URL',
)]
class ScrapeMatchesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TeamMatchRepository $teamMatchRepository,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $teams = $this->entityManager->getRepository(\App\Entity\Team::class)
            ->createQueryBuilder('t')
            ->where('t.championshipUrl IS NOT NULL')
            ->andWhere("t.championshipUrl != ''")
            ->getQuery()
            ->getResult();

        if (empty($teams)) {
            $io->warning('No teams with a championship URL configured.');
            return Command::SUCCESS;
        }

        $scriptPath = $this->projectDir . '/scripts/scrape_matches.py';

        foreach ($teams as $team) {
            $io->section(sprintf('Scraping: %s', $team->getLabel()));

            $tmpFile = tempnam(sys_get_temp_dir(), 'matches_') . '.json';

            $python = file_exists('/opt/scraper-venv/bin/python3')
                ? '/opt/scraper-venv/bin/python3'
                : 'python3';

            $command = sprintf(
                '%s %s --url %s --output %s 2>&1',
                $python,
                escapeshellarg($scriptPath),
                escapeshellarg($team->getChampionshipUrl()),
                escapeshellarg($tmpFile)
            );

            $result = [];
            $exitCode = 0;
            exec($command, $result, $exitCode);

            if ($exitCode !== 0) {
                $io->error(sprintf('Scraper failed for %s: %s', $team->getLabel(), implode("\n", $result)));
                continue;
            }

            if (!file_exists($tmpFile)) {
                $io->error(sprintf('Output file not found for %s', $team->getLabel()));
                continue;
            }

            $data = json_decode(file_get_contents($tmpFile), true);
            @unlink($tmpFile);

            if (!$data) {
                $io->warning(sprintf('No data scraped for %s', $team->getLabel()));
                continue;
            }

            $imported = 0;
            foreach ($data as $item) {
                if (!isset($item['home_team'], $item['away_team'], $item['date'])) {
                    continue;
                }

                $matchDate = $this->parseDate($item['date']);
                if (!$matchDate) {
                    continue;
                }

                // Check for existing match (upsert)
                $existing = $this->teamMatchRepository->findOneBy([
                    'team' => $team,
                    'matchDate' => $matchDate,
                    'homeTeam' => $item['home_team'],
                    'awayTeam' => $item['away_team'],
                ]);

                $match = $existing ?? new TeamMatch();
                $match->setTeam($team);
                $match->setMatchDate($matchDate);
                $match->setHomeTeam($item['home_team']);
                $match->setAwayTeam($item['away_team']);
                $match->setVenue($item['venue'] ?? null);
                $match->setMatchDay($item['match_day'] ?? null);

                // Parse score if present
                if (isset($item['score']) && preg_match('/(\d+)\s*[-–]\s*(\d+)/', $item['score'], $m)) {
                    $match->setHomeScore((int) $m[1]);
                    $match->setAwayScore((int) $m[2]);
                    $match->setStatus('played');
                } else {
                    $match->setStatus('upcoming');
                }

                $this->entityManager->persist($match);
                $imported++;
            }

            $this->entityManager->flush();
            $io->success(sprintf('%d matches imported for %s', $imported, $team->getLabel()));
        }

        return Command::SUCCESS;
    }

    private function parseDate(string $dateStr): ?\DateTimeImmutable
    {
        // Try common french date formats
        $formats = [
            'd/m/Y H:i',
            'd/m/Y H\hi',
            'd/m/Y',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
            'd-m-Y H:i',
            'd-m-Y',
        ];

        $cleaned = trim(preg_replace('/\s+/', ' ', $dateStr));

        foreach ($formats as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $cleaned);
            if ($date !== false) {
                return $date;
            }
        }

        // Try strtotime as last resort
        $ts = strtotime($cleaned);
        if ($ts !== false) {
            return new \DateTimeImmutable('@' . $ts);
        }

        return null;
    }
}
