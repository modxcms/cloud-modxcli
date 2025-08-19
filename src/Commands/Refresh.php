<?php

namespace MODX\CloudCLI\Commands;

use Symfony\Component\Console\Output\OutputInterface;

class Refresh extends Command
{
    public function __invoke(OutputInterface $output): void
    {
        $output->writeln("Clearing Cache.");
        $this->modx->getCacheManager();
        $this->modx->cacheManager->refresh();
    }
}