<?php

namespace App\Tests\Unit\Command;

use App\Command\CreateAdminUserCommand;
use App\Entity\AdminUser;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateAdminUserCommandTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private CreateAdminUserCommand $command;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->command = new CreateAdminUserCommand($this->entityManager, $this->passwordHasher);

        $application = new Application();
        $application->add($this->command);
    }

    public function testExecuteCreatesAdminUserAndPersists(): void
    {
        $this->passwordHasher
            ->expects(self::once())
            ->method('hashPassword')
            ->with(self::isInstanceOf(AdminUser::class), 'secret123')
            ->willReturn('hashed_secret123');

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(AdminUser::class));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $tester = new CommandTester($this->command);
        $exitCode = $tester->execute([
            'email' => 'admin@mjv.fr',
            'password' => 'secret123',
        ]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('admin@mjv.fr', $tester->getDisplay());
    }

    public function testExecuteDisplaysSuccessMessageWithEmail(): void
    {
        $this->passwordHasher->method('hashPassword')->willReturn('hashed');
        $this->entityManager->method('persist');
        $this->entityManager->method('flush');

        $tester = new CommandTester($this->command);
        $tester->execute([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        self::assertStringContainsString('test@example.com', $tester->getDisplay());
        self::assertStringContainsString('created successfully', $tester->getDisplay());
    }

    public function testExecuteSetsAdminRole(): void
    {
        $capturedUser = null;

        $this->passwordHasher->method('hashPassword')->willReturnCallback(
            function (AdminUser $user, string $plainPassword) use (&$capturedUser): string {
                $capturedUser = $user;
                return 'hashed_' . $plainPassword;
            }
        );

        $this->entityManager->method('persist');
        $this->entityManager->method('flush');

        $tester = new CommandTester($this->command);
        $tester->execute([
            'email' => 'admin@test.fr',
            'password' => 'mypassword',
        ]);

        self::assertNotNull($capturedUser);
        self::assertContains('ROLE_ADMIN', $capturedUser->getRoles());
    }

    public function testExecuteSetsHashedPassword(): void
    {
        $persistedUser = null;

        $this->passwordHasher->method('hashPassword')->willReturn('super_hashed_password');

        $this->entityManager
            ->method('persist')
            ->willReturnCallback(function (AdminUser $user) use (&$persistedUser): void {
                $persistedUser = $user;
            });

        $this->entityManager->method('flush');

        $tester = new CommandTester($this->command);
        $tester->execute([
            'email' => 'admin@test.fr',
            'password' => 'plainpassword',
        ]);

        self::assertNotNull($persistedUser);
        self::assertSame('super_hashed_password', $persistedUser->getPassword());
    }

    public function testCommandNameIsAppCreateAdmin(): void
    {
        self::assertSame('app:create-admin', $this->command->getName());
    }

    public function testCommandRequiresEmailArgument(): void
    {
        $definition = $this->command->getDefinition();
        self::assertTrue($definition->hasArgument('email'));
        self::assertTrue($definition->getArgument('email')->isRequired());
    }

    public function testCommandRequiresPasswordArgument(): void
    {
        $definition = $this->command->getDefinition();
        self::assertTrue($definition->hasArgument('password'));
        self::assertTrue($definition->getArgument('password')->isRequired());
    }
}
