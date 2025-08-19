<?php

namespace MODX\CloudCLI\Commands;

use MODX\CloudCLI\Services\MODX;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Config
{
    public function __invoke(InputInterface $input, OutputInterface $output): void
    {
        $verbose = $input->getOption('verbose');
        $corePath = $input->getOption('core-path');
        $configKey = $input->getOption('config-key');
        $modx = new MODX(false);
        if ($corePath) {
            if ($verbose) {
                $output->writeln("Setting core path to: ".$corePath);
            }
            $modx->setConfig('core_path', $corePath);;
        }
        if ($configKey) {
            if ($verbose) {
                $output->writeln("Setting config key to: ".$configKey);
            }
            $modx->setConfig('config_key', $configKey);
        }
        if ($verbose) {
            $output->writeln('Attempting to load MODX...');
        }
        try {
            $checkModx = new MODX();
            if ($verbose) {
                $output->writeln('MODX loaded.');
            }
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
        }
    }
}