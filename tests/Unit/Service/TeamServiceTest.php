<?php

namespace App\Tests\Unit\Service;

use App\Entity\Team;
use App\Enum\CategoryEnum;
use App\Repository\TeamRepository;
use App\Service\TeamService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TeamServiceTest extends TestCase
{
    private TeamRepository&MockObject $repository;
    private TeamService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TeamRepository::class);
        $this->service = new TeamService($this->repository);
    }

    /**
     * @dataProvider provideGroupedTeams
     */
    public function testGetAllWithCategoryLabelGroupsTeamsByCategory(array $teams, int $expectedGroups, string $expectedLabel): void
    {
        $this->repository->expects(self::once())
            ->method('findAll')
            ->willReturn($teams);

        $result = $this->service->getAllWithCategoryLabel();

        self::assertCount($expectedGroups, $result);
        self::assertSame($expectedLabel, $result[0]['categoryLabel']);
        self::assertCount(2, $result[0]['teams'], 'Both teams in the same category should be grouped together.');
    }

    public function testGetAllWithCategoryLabelSkipsTeamsWithNullCategory(): void
    {
        $teamWithCategory = $this->createTeam('U15 M', CategoryEnum::U15->value, true);
        $teamWithoutCategory = new Team();
        $teamWithoutCategory->setLabel('Unknown');
        $teamWithoutCategory->setCoach('Coach');

        $this->repository->method('findAll')->willReturn([$teamWithCategory, $teamWithoutCategory]);

        $result = $this->service->getAllWithCategoryLabel();

        self::assertCount(1, $result);
        self::assertSame('U15', $result[0]['categoryLabel']);
    }

    public function testGetAllWithCategoryLabelGroupsAreSortedByCategoryValue(): void
    {
        $teams = [
            $this->createTeam('Seniors M', CategoryEnum::SENIORS->value, true),
            $this->createTeam('U7 M', CategoryEnum::U7->value, true),
            $this->createTeam('U15 M', CategoryEnum::U15->value, true),
        ];

        $this->repository->method('findAll')->willReturn($teams);

        $result = $this->service->getAllWithCategoryLabel();

        self::assertCount(3, $result);
        // ksort by category int value means U7(2) < U15(6) < SENIORS(8)
        self::assertSame('U7', $result[0]['categoryLabel']);
        self::assertSame('U15', $result[1]['categoryLabel']);
        self::assertSame('Seniors', $result[2]['categoryLabel']);
    }

    public function testGetAllDelegatesToRepository(): void
    {
        $teams = [$this->createTeam('U15 M', CategoryEnum::U15->value, true)];
        $this->repository->expects(self::once())->method('findAll')->willReturn($teams);

        self::assertSame($teams, $this->service->getAll());
    }

    public function testFindAllByCategorySlugReturnsSortedTeams(): void
    {
        $teams = [
            $this->createTeam('U15 F', CategoryEnum::U15->value, false),
            $this->createTeam('U15 M', CategoryEnum::U15->value, true),
        ];

        $this->repository->expects(self::once())
            ->method('findBy')
            ->with(['category' => CategoryEnum::U15->value], ['gender' => 'DESC', 'label' => 'ASC'])
            ->willReturn($teams);

        $result = $this->service->findAllByCategorySlug('u15');

        self::assertSame($teams, $result);
    }

    public function testFindAllByCategorySlugReturnsEmptyArrayForUnknownSlug(): void
    {
        $this->repository->expects(self::never())->method('findBy');

        $result = $this->service->findAllByCategorySlug('unknown-slug');

        self::assertSame([], $result);
    }

    public function testFindBySlugReturnsFirstTeam(): void
    {
        $team = $this->createTeam('U15 M', CategoryEnum::U15->value, true);
        $this->repository->method('findBy')->willReturn([$team]);

        $result = $this->service->findBySlug('u15');

        self::assertSame($team, $result);
    }

    public function testFindBySlugReturnsNullWhenNoTeamsFound(): void
    {
        $this->repository->method('findBy')->willReturn([]);

        self::assertNull($this->service->findBySlug('u15'));
    }

    public function testFindBySlugReturnsNullForUnknownSlug(): void
    {
        $this->repository->expects(self::never())->method('findBy');

        self::assertNull($this->service->findBySlug('nonexistent'));
    }

    public function testFindOneDelegatesToRepository(): void
    {
        $team = $this->createTeam('U15 M', CategoryEnum::U15->value, true);
        $this->repository->expects(self::once())->method('find')->with(42)->willReturn($team);

        self::assertSame($team, $this->service->findOne(42));
    }

    public function testFindOneReturnsNullWhenNotFound(): void
    {
        $this->repository->method('find')->willReturn(null);

        self::assertNull($this->service->findOne(999));
    }

    public function testGetCategoryLabelFromIntValue(): void
    {
        self::assertSame('U15', $this->service->getCategoryLabel(CategoryEnum::U15->value));
        self::assertSame('Seniors', $this->service->getCategoryLabel(CategoryEnum::SENIORS->value));
        self::assertSame('Baby', $this->service->getCategoryLabel(CategoryEnum::BABY->value));
    }

    public function testGetCategoryLabelFromEnumCase(): void
    {
        self::assertSame('U15', $this->service->getCategoryLabel(CategoryEnum::U15));
        self::assertSame('Seniors', $this->service->getCategoryLabel(CategoryEnum::SENIORS));
    }

    public function testGetCategoryLabelFallsBackToStringCastForUnknownValue(): void
    {
        $result = $this->service->getCategoryLabel(999);
        self::assertSame('999', $result);
    }

    public function testGetCategorySlugFromIntValue(): void
    {
        self::assertSame('u15', $this->service->getCategorySlug(CategoryEnum::U15->value));
        self::assertSame('seniors', $this->service->getCategorySlug(CategoryEnum::SENIORS->value));
    }

    public function testGetCategorySlugFromEnumCase(): void
    {
        self::assertSame('u15', $this->service->getCategorySlug(CategoryEnum::U15));
        self::assertSame('loisirs', $this->service->getCategorySlug(CategoryEnum::LOISIRS));
    }

    public function testGetCategorySlugFallsBackToLowercaseStringForUnknownValue(): void
    {
        $result = $this->service->getCategorySlug(999);
        self::assertSame('999', $result);
    }

    public function testGetCategoryFromSlugReturnsCorrectEnum(): void
    {
        self::assertSame(CategoryEnum::U15, $this->service->getCategoryFromSlug('u15'));
        self::assertSame(CategoryEnum::SENIORS, $this->service->getCategoryFromSlug('seniors'));
        self::assertSame(CategoryEnum::LOISIRS, $this->service->getCategoryFromSlug('loisirs'));
    }

    public function testGetCategoryFromSlugReturnsNullForUnknownSlug(): void
    {
        self::assertNull($this->service->getCategoryFromSlug('unknown'));
    }

    public function testEnsureTeamMatchesSlug(): void
    {
        $team = $this->createTeam('U15 M', CategoryEnum::U15->value, true);

        self::assertTrue($this->service->ensureTeamMatchesSlug($team, 'u15'));
        self::assertFalse($this->service->ensureTeamMatchesSlug($team, 'seniors'));
    }

    public function testGetMenuGroupsSeparatesYouthFromAdult(): void
    {
        $teams = [
            $this->createTeam('U15 M', CategoryEnum::U15->value, true),
            $this->createTeam('U7 M', CategoryEnum::U7->value, true),
            $this->createTeam('Seniors M', CategoryEnum::SENIORS->value, true),
            $this->createTeam('Loisirs M', CategoryEnum::LOISIRS->value, true),
        ];

        $this->repository->method('findAll')->willReturn($teams);

        $result = $this->service->getMenuGroups();

        self::assertArrayHasKey('youth', $result);
        self::assertArrayHasKey('adult', $result);
        self::assertCount(2, $result['youth']);
        self::assertCount(2, $result['adult']);
    }

    public function testGetMenuGroupsDeduplicatesCategories(): void
    {
        $teams = [
            $this->createTeam('U15 M', CategoryEnum::U15->value, true),
            $this->createTeam('U15 F', CategoryEnum::U15->value, false),
        ];

        $this->repository->method('findAll')->willReturn($teams);

        $result = $this->service->getMenuGroups();

        // Two teams in the same category should produce only one menu entry
        self::assertCount(1, $result['youth']);
        self::assertCount(0, $result['adult']);
        self::assertSame('u15', $result['youth'][0]['slug']);
        self::assertSame('U15', $result['youth'][0]['label']);
    }

    public function testGetMenuGroupsYouthIsSortedAlphabetically(): void
    {
        $teams = [
            $this->createTeam('U18 M', CategoryEnum::U18->value, true),
            $this->createTeam('Baby M', CategoryEnum::BABY->value, true),
            $this->createTeam('U7 M', CategoryEnum::U7->value, true),
        ];

        $this->repository->method('findAll')->willReturn($teams);

        $result = $this->service->getMenuGroups();

        $labels = array_column($result['youth'], 'label');
        self::assertSame(['Baby', 'U18', 'U7'], $labels);
    }

    public function testGetMenuGroupsSkipsTeamsWithNullCategory(): void
    {
        $teamWithoutCategory = new Team();
        $teamWithoutCategory->setLabel('Orphan');
        $teamWithoutCategory->setCoach('Coach');

        $this->repository->method('findAll')->willReturn([$teamWithoutCategory]);

        $result = $this->service->getMenuGroups();

        self::assertCount(0, $result['youth']);
        self::assertCount(0, $result['adult']);
    }

    public function testGetAllWithCategoryLabelReturnsEmptyArrayWhenNoTeams(): void
    {
        $this->repository->method('findAll')->willReturn([]);

        $result = $this->service->getAllWithCategoryLabel();

        self::assertSame([], $result);
    }

    public function testGetMenuGroupsAdultIsSortedAlphabetically(): void
    {
        $teams = [
            $this->createTeam('Seniors M', CategoryEnum::SENIORS->value, true),
            $this->createTeam('Loisirs M', CategoryEnum::LOISIRS->value, true),
        ];

        $this->repository->method('findAll')->willReturn($teams);

        $result = $this->service->getMenuGroups();

        $labels = array_column($result['adult'], 'label');
        self::assertSame(['Loisirs', 'Seniors'], $labels);
    }

    public function testGetMenuGroupsEmptyWhenNoTeams(): void
    {
        $this->repository->method('findAll')->willReturn([]);

        $result = $this->service->getMenuGroups();

        self::assertSame([], $result['youth']);
        self::assertSame([], $result['adult']);
    }

    public function testFindAllByCategorySlugReturnsEmptyForUnknownCategory(): void
    {
        $this->repository->expects(self::never())->method('findBy');

        $result = $this->service->findAllByCategorySlug('nonexistent');

        self::assertSame([], $result);
    }

    public function testGetCategoryLabelFallsBackToStringCastForNonMatchingInt(): void
    {
        // Passing an int that doesn't match any enum case
        $result = $this->service->getCategoryLabel(999);

        self::assertSame('999', $result);
    }

    public static function provideGroupedTeams(): iterable
    {
        $makeTeam = static function (string $label, int $category, ?bool $gender): Team {
            $team = new Team();
            $team->setLabel($label);
            $team->setCategory($category);
            $team->setGender($gender);
            $team->setCoach('Coach ' . $label);
            return $team;
        };

        yield 'u15 mixed' => [
            [
                $makeTeam('U15 M', CategoryEnum::U15->value, true),
                $makeTeam('U15 F', CategoryEnum::U15->value, false),
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
