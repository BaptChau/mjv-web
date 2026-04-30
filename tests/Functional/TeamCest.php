<?php

namespace App\Tests\Functional;

use App\Tests\Functional\Support\FunctionalTester;

class TeamCest
{
    public function teamIndexWorks(FunctionalTester $I): void
    {
        $I->amOnPage('/equipes');
        $I->seeResponseCodeIs(200);
    }

    public function teamDetailsReturns404ForUnknownSlug(FunctionalTester $I): void
    {
        $I->amOnPage('/equipes/unknown-category');
        $I->seeResponseCodeIs(404);
    }
}
