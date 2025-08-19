<?php

namespace MODX\CloudCLI\Commands\Plugins;

use MODX\CloudCLI\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Plugin extends Command
{
    private $plugin;

    private function prepare(InputInterface $input, OutputInterface $output): bool
    {
        $nameOrID = $input->getArgument('nameOrID');
        $verbose = $input->getOption('verbose');
        if (empty($nameOrID)) {
            $output->writeln("No plugin name or ID provided.");
            return false;
        }

        if ((int) $nameOrID > 0) {
            if ($verbose) {
                $output->writeln("Checking for plugin ID: " . $nameOrID);
            }
            $plugin = $this->modx->getObject('modPlugin', ['id' => $nameOrID]);
        } else {
            if ($verbose) {
                $output->writeln("Checking for plugin name: " . $nameOrID);
            }
            $plugin = $this->modx->getObject('modPlugin', ['name' => $nameOrID]);
        }

        if (empty($plugin)) {
            $output->writeln("Plugin not found.");
            return false;
        }
        $this->plugin = $plugin;
        return true;
    }

    public function enable(InputInterface $input, OutputInterface $output): void
    {
        $verbose = $input->getOption('verbose');
        if (!$this->prepare($input, $output)) {
            return;
        }
        if (!$this->plugin->get('disabled')) {
            $output->writeln("Plugin is already enabled.");
            return;
        }
        $this->plugin->set('disabled', 0);
        if ($this->plugin->save()) {
            if ($verbose) {
                $output->writeln("Plugin enabled.");
            }
            $this->modx->cacheManager->refresh();
        } else {
            $output->writeln("Plugin could not be enabled.");
        }
    }

    public function disable(InputInterface $input, OutputInterface $output): void
    {
        $verbose = $input->getOption('verbose');
        if (!$this->prepare($input, $output)) {
            return;
        }
        if ($this->plugin->get('disabled')) {
            $output->writeln("Plugin is already disabled.");
            return;
        }
        $this->plugin->set('disabled', 1);
        if ($this->plugin->save()) {
            if ($verbose) {
                $output->writeln("Plugin disabled.");
            }
            $this->modx->cacheManager->refresh();
        } else {
            $output->writeln("Plugin could not be disabled.");
        }
    }
}