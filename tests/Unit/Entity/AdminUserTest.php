<?php

namespace App\Tests\Unit\Entity;

use App\Entity\AdminUser;
use PHPUnit\Framework\TestCase;

class AdminUserTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $user = new AdminUser();

        $user->setEmail('admin@mjv.fr');
        self::assertSame('admin@mjv.fr', $user->getEmail());
        self::assertSame('admin@mjv.fr', $user->getUserIdentifier());

        $user->setPassword('hashed');
        self::assertSame('hashed', $user->getPassword());

        self::assertNull($user->getId());
    }

    public function testRolesAlwaysContainRoleUser(): void
    {
        $user = new AdminUser();

        self::assertContains('ROLE_USER', $user->getRoles());

        $user->setRoles(['ROLE_ADMIN']);
        $roles = $user->getRoles();
        self::assertContains('ROLE_ADMIN', $roles);
        self::assertContains('ROLE_USER', $roles);
    }

    public function testRolesAreUnique(): void
    {
        $user = new AdminUser();
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_USER']);

        $roles = $user->getRoles();
        self::assertCount(count(array_unique($roles)), $roles);
    }

    public function testEraseCredentials(): void
    {
        $user = new AdminUser();
        $user->eraseCredentials();

        // Should not throw, method is intentionally empty
        self::assertTrue(true);
    }

    public function testFieldsAreNullByDefault(): void
    {
        $user = new AdminUser();

        self::assertNull($user->getEmail());
        self::assertNull($user->getPassword());
    }

    public function testEmptyRolesArrayAlwaysContainsRoleUser(): void
    {
        $user = new AdminUser();
        $user->setRoles([]);

        self::assertContains('ROLE_USER', $user->getRoles());
        self::assertCount(1, $user->getRoles());
    }

    public function testSettersReturnStaticForFluentInterface(): void
    {
        $user = new AdminUser();

        self::assertInstanceOf(AdminUser::class, $user->setEmail('admin@mjv.fr'));
        self::assertInstanceOf(AdminUser::class, $user->setPassword('hashed'));
        self::assertInstanceOf(AdminUser::class, $user->setRoles(['ROLE_ADMIN']));
    }

    public function testGetUserIdentifierReturnsEmptyStringWhenEmailIsNull(): void
    {
        $user = new AdminUser();

        self::assertSame('', $user->getUserIdentifier());
    }
}
