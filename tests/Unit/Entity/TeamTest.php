<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Team;
use PHPUnit\Framework\TestCase;

class TeamTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $team = new Team();

        $team->setLabel('U15 M');
        self::assertSame('U15 M', $team->getLabel());

        $team->setCoach('Jean Dupont');
        self::assertSame('Jean Dupont', $team->getCoach());

        $team->setSecondCoach('Marie Martin');
        self::assertSame('Marie Martin', $team->getSecondCoach());

        $team->setGender(true);
        self::assertTrue($team->isGender());

        $team->setCategory(6);
        self::assertSame(6, $team->getCategory());

        $team->setPhotoPath('/uploads/teams/u15m.jpg');
        self::assertSame('/uploads/teams/u15m.jpg', $team->getPhotoPath());

        self::assertNull($team->getId());
    }

    public function testSecondCoachIsNullable(): void
    {
        $team = new Team();
        $team->setSecondCoach(null);

        self::assertNull($team->getSecondCoach());
    }

    public function testGenderIsNullable(): void
    {
        $team = new Team();
        $team->setGender(null);

        self::assertNull($team->isGender());
    }

    public function testChampionshipUrlGetterAndSetter(): void
    {
        $team = new Team();

        self::assertNull($team->getChampionshipUrl());

        $url = 'https://www.ffhandball.fr/competitions/championship/123';
        $team->setChampionshipUrl($url);
        self::assertSame($url, $team->getChampionshipUrl());
    }

    public function testChampionshipUrlCanBeSetToNull(): void
    {
        $team = new Team();
        $team->setChampionshipUrl('https://example.com');
        $team->setChampionshipUrl(null);

        self::assertNull($team->getChampionshipUrl());
    }

    public function testToStringReturnsLabel(): void
    {
        $team = new Team();
        $team->setLabel('Seniors M');

        self::assertSame('Seniors M', (string) $team);
    }

    public function testToStringReturnsEmptyStringWhenLabelIsNull(): void
    {
        $team = new Team();

        self::assertSame('', (string) $team);
    }

    public function testSettersReturnStaticForFluentInterface(): void
    {
        $team = new Team();

        self::assertInstanceOf(Team::class, $team->setLabel('U15 M'));
        self::assertInstanceOf(Team::class, $team->setCoach('Coach'));
        self::assertInstanceOf(Team::class, $team->setSecondCoach(null));
        self::assertInstanceOf(Team::class, $team->setGender(true));
        self::assertInstanceOf(Team::class, $team->setCategory(6));
        self::assertInstanceOf(Team::class, $team->setPhotoPath('/img.jpg'));
        self::assertInstanceOf(Team::class, $team->setChampionshipUrl(null));
    }
}
