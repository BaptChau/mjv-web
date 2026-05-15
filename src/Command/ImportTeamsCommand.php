<?php

namespace App\Command;

use App\Entity\Team;
use App\Enum\CategoryEnum;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:import-teams',
    description: 'Import teams from src/import.json into the database',
)]
class ImportTeamsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TeamRepository $teamRepository,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite existing teams with the same label and category');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $overwrite = $input->getOption('overwrite');

        $file = $this->projectDir . '/src/import.json';
        if (!file_exists($file)) {
            $io->error('File not found: src/import.json');
            return Command::FAILURE;
        }

        $data = json_decode(file_get_contents($file), true);
        if (!$data) {
            $io->error('Invalid JSON in src/import.json');
            return Command::FAILURE;
        }

        $categoryMap = [
            'SENIOR'  => CategoryEnum::SENIORS,
            'LOISIRS' => CategoryEnum::LOISIRS,
            'U18'     => CategoryEnum::U18,
            'U15'     => CategoryEnum::U15,
            'U13'     => CategoryEnum::U13,
            'U11'     => CategoryEnum::U11,
            'U9'      => CategoryEnum::U9,
            'U7'      => CategoryEnum::U7,
            'BABY'    => CategoryEnum::BABY,
        ];

        $imported = 0;
        $skipped = 0;

        foreach ($data as $categoryKey => $teams) {
            $enum = $categoryMap[strtoupper($categoryKey)] ?? null;
            if ($enum === null) {
                $io->warning(sprintf('Unknown category "%s", skipping.', $categoryKey));
                continue;
            }

            foreach ($teams as $teamData) {
                $label = $teamData['label'] ?? null;
                if (!$label) {
                    continue;
                }

                $existing = $this->teamRepository->findOneBy([
                    'label'    => $label,
                    'category' => $enum->value,
                ]);

                if ($existing && !$overwrite) {
                    $io->note(sprintf('Skipping existing team "%s" (%s)', $label, $categoryKey));
                    $skipped++;
                    continue;
                }

                $team = $existing ?? new Team();
                $team->setLabel($label);
                $team->setCategory($enum->value);
                $team->setGender($teamData['gender']);
                $team->setCoach($teamData['coach'] ?? '');
                $team->setSecondCoach($teamData['secondCoach']);

                $this->entityManager->persist($team);
                $imported++;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d team(s) imported, %d skipped.', $imported, $skipped));

        return Command::SUCCESS;
    }
}
