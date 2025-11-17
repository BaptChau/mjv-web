<?php

namespace App\Tests\Service;

use App\Entity\Team;
use App\Repository\TeamRepository;
use App\Service\TeamService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TeamServiceTest extends TestCase
{
    /**
     * @dataProvider provideGroupedTeams
     */
    public function testGetAllWithCategoryLabelGroupsTeamsByCategory(array $teams, int $expectedGroups, string $expectedLabel): void
    {
        /** @var TeamRepository&MockObject $repository */
        $repository = $this->createMock(TeamRepository::class);
        $repository->expects(self::once())
            ->method('findAll')
            ->willReturn($teams);

        $service = new TeamService($repository);

        $result = $service->getAllWithCategoryLabel();

        self::assertCount($expectedGroups, $result);
        self::assertSame($expectedLabel, $result[0]['categoryLabel']);
        self::assertCount(2, $result[0]['teams'], 'Both teams in the same category should be grouped together.');
    }

    public function testFindAllByCategorySlugReturnsSortedTeams(): void
    {
        $teams = [
            $this->createTeam('U15 F', 5, false),
            $this->createTeam('U15 M', 5, true),
        ];

        /** @var TeamRepository&MockObject $repository */
        $repository = $this->createMock(TeamRepository::class);
        $repository->expects(self::once())
            ->method('findBy')
            ->with(['category' => 5], ['gender' => 'DESC', 'label' => 'ASC'])
            ->willReturn($teams);

        $service = new TeamService($repository);
        $result = $service->findAllByCategorySlug('u15');

        self::assertSame($teams, $result);
    }

    public function testEnsureTeamMatchesSlug(): void
    {
        $team = $this->createTeam('U15 M', 5, true);

        /** @var TeamRepository&MockObject $repository */
        $repository = $this->createMock(TeamRepository::class);
        $service = new TeamService($repository);

        self::assertTrue($service->ensureTeamMatchesSlug($team, 'u15'));
        self::assertFalse($service->ensureTeamMatchesSlug($team, 'seniors'));
    }

    public function provideGroupedTeams(): iterable
    {
        yield 'u15 mixed' => [
            [
                $this->createTeam('U15 M', 5, true),
                $this->createTeam('U15 F', 5, false),
            ],
            1,
            'U15',
        ];
    }

    private function createTeam(string $label, int $category, ?bool $gender): Team
    {
        $team = new Team();
        $team->setLabel($label);
        $team->setCategory($category);
        $team->setGender($gender);
        $team->setCoach('Coach ' . $label);

        return $team;
    }
}
