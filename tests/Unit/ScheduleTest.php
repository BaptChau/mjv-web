<?php

namespace App\Tests\Unit;

use App\Message\ScrapeMatchesMessage;
use App\Schedule;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule as SymfonySchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

class ScheduleTest extends TestCase
{
    private Schedule $schedule;

    protected function setUp(): void
    {
        $this->schedule = new Schedule(new ArrayAdapter());
    }

    public function testImplementsScheduleProviderInterface(): void
    {
        self::assertInstanceOf(ScheduleProviderInterface::class, $this->schedule);
    }

    public function testGetScheduleReturnsScheduleInstance(): void
    {
        self::assertInstanceOf(SymfonySchedule::class, $this->schedule->getSchedule());
    }

    public function testGetScheduleContainsExactlyOneRecurringMessage(): void
    {
        $messages = $this->schedule->getSchedule()->getRecurringMessages();

        self::assertCount(1, $messages);
        self::assertInstanceOf(RecurringMessage::class, $messages[0]);
    }

    public function testGetScheduleDispatchesScrapeMatchesMessage(): void
    {
        $message = $this->schedule->getSchedule()->getRecurringMessages()[0];
        $context = new \Symfony\Component\Scheduler\Generator\MessageContext(
            'default',
            $message->getId(),
            $message->getTrigger(),
            new \DateTimeImmutable(),
        );

        $payloads = iterator_to_array($message->getMessages($context), false);

        self::assertCount(1, $payloads);
        self::assertInstanceOf(ScrapeMatchesMessage::class, $payloads[0]);
    }

    public function testGetScheduleUsesNightlyAt3amCronExpression(): void
    {
        $trigger = $this->schedule->getSchedule()->getRecurringMessages()[0]->getTrigger();

        self::assertSame('0 3 * * *', (string) $trigger);
    }

    public function testScheduleIsStatefulAndOnlyProcessesLastMissedRun(): void
    {
        $schedule = $this->schedule->getSchedule();

        self::assertNotNull($schedule->getState());
        self::assertTrue($schedule->shouldProcessOnlyLastMissedRun());
    }
}
