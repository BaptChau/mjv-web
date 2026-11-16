<?php

namespace App\Tests\Functional;

use App\Tests\Functional\Support\FunctionalTester;
use Codeception\Attribute\DataProvider;
use Codeception\Example;

class AdminSecurityCest
{
    public function loginPageIsAccessible(FunctionalTester $I): void
    {
        $I->amOnPage('/mjv-admin-connect');
        $I->seeResponseCodeIs(200);
    }

    /**
     * Anonymous users must be redirected to the admin login for every admin
     * route, including EasyAdmin CRUD routes (guards against GHSA-g2fm-8hr4-j82h).
     */
    #[DataProvider('adminUrls')]
    public function adminRoutesRedirectAnonymousUsersToLogin(FunctionalTester $I, Example $example): void
    {
        $I->stopFollowingRedirects();
        $I->amOnPage($example['url']);
        $I->seeResponseCodeIsRedirection();

        $I->followRedirect();
        $I->seeCurrentUrlEquals('/mjv-admin-connect');
        $I->seeResponseCodeIs(200);
    }

    protected function adminUrls(): array
    {
        return [
            ['url' => '/admin'],
            ['url' => '/admin/contact'],
            ['url' => '/admin/forum-post'],
            ['url' => '/admin/contact/new'],
        ];
    }
}
