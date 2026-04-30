<?php

namespace App\Tests\Functional;

use App\Tests\Functional\Support\FunctionalTester;

class MainCest
{
    public function homepageWorks(FunctionalTester $I): void
    {
        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
    }

    public function clubPageWorks(FunctionalTester $I): void
    {
        $I->amOnPage('/club');
        $I->seeResponseCodeIs(200);
    }
}
