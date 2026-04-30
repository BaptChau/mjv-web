<?php

namespace App\Tests\Functional;

use App\Tests\Functional\Support\FunctionalTester;

class ContactCest
{
    public function contactPageWorks(FunctionalTester $I): void
    {
        $I->amOnPage('/contact');
        $I->seeResponseCodeIs(200);
    }
}
