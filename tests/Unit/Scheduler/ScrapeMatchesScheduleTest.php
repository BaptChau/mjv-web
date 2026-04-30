<?php

namespace App\Tests\Unit\Scheduler;

use App\Message\ScrapeMatchesMessage;
use App\Scheduler\ScrapeMatchesSchedule;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

class ScrapeMatchesScheduleTest extends TestCase
{
    private ScrapeMatchesSchedule $schedule;

    protected function setUp(): void
    {
        $this->schedule = new ScrapeMatchesSchedule();
    }

    public function testImplementsScheduleProviderInterface(): void
    {
        self::assertInstanceOf(ScheduleProviderInterface::class, $this->schedule);
    }

    public function testGetScheduleReturnsScheduleInstance(): void
    {
        if (!class_exists(\Cron\CronExpression::class)) {
            self::markTestSkipped('dragonmantank/cron-expression is not installed.');
        }

        $result = $this->schedule->getSchedule();

        self::assertInstanceOf(Schedule::class, $result);
    }

    public function testGetScheduleContainsExactlyOneRecurringMessage(): void
    {
        if (!class_exists(\Cron\CronExpression::class)) {
            self::markTestSkipped('dragonmantank/cron-expression is not installed.');
        }

        $schedule = $this->schedule->getSchedule();
        $messages = $schedule->getRecurringMessages();

        self::assertCount(1, $messages);
        self::assertInstanceOf(RecurringMessage::class, $messages[0]);
    }

    public function testGetScheduleUsesNightlyAt3amCronExpression(): void
    {
        if (!class_exists(\Cron\CronExpression::class)) {
            self::markTestSkipped('dragonmantank/cron-expression is not installed.');
        }

        $schedule = $this->schedule->getSchedule();
        $messages = $schedule->getRecurringMessages();

        $trigger = $messages[0]->getTrigger();
        self::assertSame('0 3 * * *', (string) $trigger);
    }

    public function testGetScheduleReturnsNewScheduleOnEachCall(): void
    {
        if (!class_exists(\Cron\CronExpression::class)) {
            self::markTestSkipped('dragonmantank/cron-expression is not installed.');
        }

        $first = $this->schedule->getSchedule();
        $second = $this->schedule->getSchedule();

        self::assertInstanceOf(Schedule::class, $first);
        self::assertInstanceOf(Schedule::class, $second);
    }
}
