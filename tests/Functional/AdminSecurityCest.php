<?php

namespace App\Tests\Functional;

use App\Tests\Functional\Support\FunctionalTester;

class AdminSecurityCest
{
    public function loginPageIsAccessible(FunctionalTester $I): void
    {
        $I->amOnPage('/mjv-admin-connect');
        $I->seeResponseCodeIs(200);
    }

    public function adminDashboardRedirectsToLogin(FunctionalTester $I): void
    {
        $I->amOnPage('/admin');
        $I->seeResponseCodeIsRedirection();
    }
}
