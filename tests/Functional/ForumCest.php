<?php

namespace App\Tests\Functional;

use App\Tests\Functional\Support\FunctionalTester;

class ForumCest
{
    public function forumIndexWorks(FunctionalTester $I): void
    {
        $I->amOnPage('/forum');
        $I->seeResponseCodeIs(200);
    }

    public function forumPostNotFoundShowsError(FunctionalTester $I): void
    {
        $I->amOnPage('/forum/post/999999');
        $I->seeResponseCodeIs(200);
    }
}
