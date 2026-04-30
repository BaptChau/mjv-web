<?php

namespace App\Tests\Functional;

use App\Tests\Functional\Support\FunctionalTester;

class NewsCest
{
    public function newsIndexWorks(FunctionalTester $I): void
    {
        $I->amOnPage('/actualites');
        $I->seeResponseCodeIs(200);
    }

    public function newsArchivesWorks(FunctionalTester $I): void
    {
        $I->amOnPage('/actualites/archives');
        $I->seeResponseCodeIs(200);
    }

    public function newsArchivesPaginationWorks(FunctionalTester $I): void
    {
        $I->amOnPage('/actualites/archives?page=1&perPage=5');
        $I->seeResponseCodeIs(200);
    }

    public function newsDetailsReturns200OrError(FunctionalTester $I): void
    {
        $I->amOnPage('/actualites/9999');
        $I->seeResponseCodeIs(200);
    }
}
