<?php

namespace App\Tests\Unit\MessageHandler;

use App\Message\ScrapeMatchesMessage;
use App\MessageHandler\ScrapeMatchesHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class ScrapeMatchesHandlerTest extends TestCase
{
    public function testHandlerIsInstantiable(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $handler = new ScrapeMatchesHandler($kernel);

        self::assertInstanceOf(ScrapeMatchesHandler::class, $handler);
    }

    public function testMessageIsInstantiable(): void
    {
        $message = new ScrapeMatchesMessage();
        self::assertInstanceOf(ScrapeMatchesMessage::class, $message);
    }

    public function testHandlerIsTaggedAsMessageHandler(): void
    {
        $reflection = new \ReflectionClass(ScrapeMatchesHandler::class);
        $attributes = $reflection->getAttributes(AsMessageHandler::class);

        self::assertNotEmpty($attributes, 'ScrapeMatchesHandler must carry the #[AsMessageHandler] attribute.');
    }

    public function testHandlerHasInvokeMethod(): void
    {
        $reflection = new \ReflectionClass(ScrapeMatchesHandler::class);

        self::assertTrue(
            $reflection->hasMethod('__invoke'),
            'ScrapeMatchesHandler must have an __invoke method to be usable as a Messenger handler.'
        );
    }

    public function testInvokeMethodAcceptsScrapeMatchesMessage(): void
    {
        $reflection = new \ReflectionClass(ScrapeMatchesHandler::class);
        $method = $reflection->getMethod('__invoke');
        $parameters = $method->getParameters();

        self::assertCount(1, $parameters, '__invoke must accept exactly one parameter.');
        self::assertSame(
            ScrapeMatchesMessage::class,
            $parameters[0]->getType()->getName(),
            '__invoke must type-hint its parameter as ScrapeMatchesMessage.'
        );
    }
}
