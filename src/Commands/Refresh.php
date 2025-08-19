<?php

namespace MODX\CloudCLI\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Refresh extends Command
{
    public function __invoke(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln("Clearing Cache.");
        $this->modx->getCacheManager();
        $this->modx->cacheManager->refresh();
    }
}