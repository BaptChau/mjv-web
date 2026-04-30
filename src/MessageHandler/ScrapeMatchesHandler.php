<?php

namespace App\MessageHandler;

use App\Message\ScrapeMatchesMessage;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsMessageHandler]
class ScrapeMatchesHandler
{
    public function __construct(
        private KernelInterface $kernel,
    ) {
    }

    public function __invoke(ScrapeMatchesMessage $message): void
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => 'app:scrape-matches']);
        $application->run($input, new NullOutput());
    }
}
