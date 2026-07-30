<?php

namespace MODX\CloudCLI\Commands;

use MODX\CloudCLI\Services\MODX;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command
{

    /** @var \modX $modx */
    public $modx;

    public function __construct()
    {
        try {
            $this->modx = (new MODX())->modx;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function __invoke(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln("Invoking Command.");
    }

    public function getCache($key)
    {
        return $this->modx->cacheManager->get("cloudcli.$key");
    }

    public function setCache($key, $value, $ttl = 3600)
    {
        return $this->modx->cacheManager->set("cloudcli.$key", $value, $ttl);
    }
}