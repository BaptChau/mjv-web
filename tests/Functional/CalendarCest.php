<?php

namespace App\Tests\Functional;

use App\Tests\Functional\Support\FunctionalTester;

class CalendarCest
{
    public function calendarPageWorks(FunctionalTester $I): void
    {
        $I->amOnPage('/calendrier');
        $I->seeResponseCodeIs(200);
    }

    public function calendarWithDateFilterWorks(FunctionalTester $I): void
    {
        $I->amOnPage('/calendrier?date=2025-10-01');
        $I->seeResponseCodeIs(200);
    }
}
