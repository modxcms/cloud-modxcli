<?php

namespace MODX\CloudCLI;

use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;

class Application extends \Silly\Application
{

    /**
     * Gets the default commands that should always be available.
     *
     * @return Command[]
     */
    protected function getDefaultCommands()
    {
        return [new HelpCommand(), new ListCommand()];
    }
}