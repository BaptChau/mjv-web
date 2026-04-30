<?php

namespace App\Tests\Unit\Command;

use App\Command\ScrapeMatchesCommand;
use App\Repository\TeamMatchRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScrapeMatchesCommandTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private TeamMatchRepository&MockObject $teamMatchRepository;
    private \ReflectionMethod $parseDate;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->teamMatchRepository = $this->createMock(TeamMatchRepository::class);

        $command = new ScrapeMatchesCommand(
            $this->entityManager,
            $this->teamMatchRepository,
            '/tmp'
        );

        // Expose the private parseDate method for direct unit testing
        $this->parseDate = new \ReflectionMethod(ScrapeMatchesCommand::class, 'parseDate');
        $this->parseDate->setAccessible(true);
    }

    private function parseDate(string $input): ?\DateTimeImmutable
    {
        $command = new ScrapeMatchesCommand(
            $this->entityManager,
            $this->teamMatchRepository,
            '/tmp'
        );

        return $this->parseDate->invoke($command, $input);
    }

    // --- parseDate: happy-path formats ---

    public function testParseDateFrenchDateTimeWithSlashAndColonSeparator(): void
    {
        $result = $this->parseDate('15/09/2025 20:30');

        self::assertNotNull($result);
        self::assertSame('2025-09-15', $result->format('Y-m-d'));
        self::assertSame('20:30', $result->format('H:i'));
    }

    public function testParseDateFrenchDateTimeWithHSeparator(): void
    {
        $result = $this->parseDate('15/09/2025 20h30');

        self::assertNotNull($result);
        self::assertSame('2025-09-15', $result->format('Y-m-d'));
        self::assertSame('20:30', $result->format('H:i'));
    }

    public function testParseDateFrenchDateOnly(): void
    {
        $result = $this->parseDate('15/09/2025');

        self::assertNotNull($result);
        self::assertSame('2025-09-15', $result->format('Y-m-d'));
    }

    public function testParseDateIso8601DateTimeWithSeconds(): void
    {
        $result = $this->parseDate('2025-09-15 20:30:00');

        self::assertNotNull($result);
        self::assertSame('2025-09-15', $result->format('Y-m-d'));
        self::assertSame('20:30', $result->format('H:i'));
    }

    public function testParseDateIso8601DateTimeWithoutSeconds(): void
    {
        $result = $this->parseDate('2025-09-15 20:30');

        self::assertNotNull($result);
        self::assertSame('2025-09-15', $result->format('Y-m-d'));
    }

    public function testParseDateIso8601DateOnly(): void
    {
        $result = $this->parseDate('2025-09-15');

        self::assertNotNull($result);
        self::assertSame('2025-09-15', $result->format('Y-m-d'));
    }

    public function testParseDateDashSeparatedDateTimeFormat(): void
    {
        $result = $this->parseDate('15-09-2025 20:30');

        self::assertNotNull($result);
        self::assertSame('2025-09-15', $result->format('Y-m-d'));
    }

    public function testParseDateDashSeparatedDateOnly(): void
    {
        $result = $this->parseDate('15-09-2025');

        self::assertNotNull($result);
        self::assertSame('2025-09-15', $result->format('Y-m-d'));
    }

    // --- parseDate: edge cases ---

    public function testParseDateTrimsExtraWhitespace(): void
    {
        $result = $this->parseDate('  15/09/2025  ');

        self::assertNotNull($result);
        self::assertSame('2025-09-15', $result->format('Y-m-d'));
    }

    public function testParseDateCollapsesMultipleSpaces(): void
    {
        $result = $this->parseDate('15/09/2025  20:30');

        self::assertNotNull($result);
        self::assertSame('2025-09-15', $result->format('Y-m-d'));
    }

    // --- parseDate: failure cases ---

    public function testParseDateReturnsNullForGibberish(): void
    {
        $result = $this->parseDate('not-a-date-at-all!@#$');

        self::assertNull($result);
    }

    public function testParseDateReturnsNullForEmptyString(): void
    {
        $result = $this->parseDate('');

        self::assertNull($result);
    }

    public function testParseDateMidnightTime(): void
    {
        $result = $this->parseDate('01/01/2025 00:00');

        self::assertNotNull($result);
        self::assertSame('2025-01-01', $result->format('Y-m-d'));
        self::assertSame('00:00', $result->format('H:i'));
    }

    public function testParseDateEndOfYear(): void
    {
        $result = $this->parseDate('31/12/2025');

        self::assertNotNull($result);
        self::assertSame('2025-12-31', $result->format('Y-m-d'));
    }

    public function testParseDateLeapDay(): void
    {
        $result = $this->parseDate('29/02/2024');

        self::assertNotNull($result);
        self::assertSame('2024-02-29', $result->format('Y-m-d'));
    }

    public function testParseDateWithHSeparatorPaddedHour(): void
    {
        $result = $this->parseDate('05/06/2025 09h00');

        self::assertNotNull($result);
        self::assertSame('2025-06-05', $result->format('Y-m-d'));
        self::assertSame('09:00', $result->format('H:i'));
    }

    // --- Command metadata ---

    public function testCommandNameIsAppScrapeMatches(): void
    {
        $command = new ScrapeMatchesCommand(
            $this->entityManager,
            $this->teamMatchRepository,
            '/tmp'
        );

        self::assertSame('app:scrape-matches', $command->getName());
    }

    public function testCommandDescriptionIsNotEmpty(): void
    {
        $command = new ScrapeMatchesCommand(
            $this->entityManager,
            $this->teamMatchRepository,
            '/tmp'
        );

        self::assertNotEmpty($command->getDescription());
    }
}
