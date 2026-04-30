<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Team;
use App\Entity\TeamMatch;
use PHPUnit\Framework\TestCase;

class TeamMatchTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $match = new TeamMatch();
        $team = new Team();

        $match->setTeam($team);
        self::assertSame($team, $match->getTeam());

        $date = new \DateTimeImmutable('2025-09-13 20:30');
        $match->setMatchDate($date);
        self::assertSame($date, $match->getMatchDate());

        $match->setHomeTeam('Team A');
        self::assertSame('Team A', $match->getHomeTeam());

        $match->setAwayTeam('Team B');
        self::assertSame('Team B', $match->getAwayTeam());

        $match->setHomeScore(27);
        self::assertSame(27, $match->getHomeScore());

        $match->setAwayScore(30);
        self::assertSame(30, $match->getAwayScore());

        $match->setVenue('Gymnase');
        self::assertSame('Gymnase', $match->getVenue());

        $match->setStatus('played');
        self::assertSame('played', $match->getStatus());

        $match->setMatchDay('Journée 1');
        self::assertSame('Journée 1', $match->getMatchDay());
    }

    public function testDefaultStatus(): void
    {
        $match = new TeamMatch();
        self::assertSame('upcoming', $match->getStatus());
    }

    public function testNullableFields(): void
    {
        $match = new TeamMatch();
        self::assertNull($match->getHomeScore());
        self::assertNull($match->getAwayScore());
        self::assertNull($match->getVenue());
        self::assertNull($match->getMatchDay());
    }

    public function testIdIsNullByDefault(): void
    {
        $match = new TeamMatch();

        self::assertNull($match->getId());
    }

    public function testTeamIsNullByDefault(): void
    {
        $match = new TeamMatch();

        self::assertNull($match->getTeam());
    }

    public function testMatchDateIsNullByDefault(): void
    {
        $match = new TeamMatch();

        self::assertNull($match->getMatchDate());
    }

    public function testScoresCanBeSetToNull(): void
    {
        $match = new TeamMatch();
        $match->setHomeScore(10);
        $match->setHomeScore(null);
        $match->setAwayScore(20);
        $match->setAwayScore(null);

        self::assertNull($match->getHomeScore());
        self::assertNull($match->getAwayScore());
    }

    public function testTeamCanBeSetToNull(): void
    {
        $match = new TeamMatch();
        $team = new \App\Entity\Team();
        $match->setTeam($team);
        $match->setTeam(null);

        self::assertNull($match->getTeam());
    }

    public function testSettersReturnStaticForFluentInterface(): void
    {
        $match = new TeamMatch();
        $team = new \App\Entity\Team();
        $date = new \DateTimeImmutable();

        self::assertInstanceOf(TeamMatch::class, $match->setTeam($team));
        self::assertInstanceOf(TeamMatch::class, $match->setMatchDate($date));
        self::assertInstanceOf(TeamMatch::class, $match->setHomeTeam('Home'));
        self::assertInstanceOf(TeamMatch::class, $match->setAwayTeam('Away'));
        self::assertInstanceOf(TeamMatch::class, $match->setHomeScore(10));
        self::assertInstanceOf(TeamMatch::class, $match->setAwayScore(8));
        self::assertInstanceOf(TeamMatch::class, $match->setVenue('Gym'));
        self::assertInstanceOf(TeamMatch::class, $match->setStatus('played'));
        self::assertInstanceOf(TeamMatch::class, $match->setMatchDay('J1'));
    }
}
