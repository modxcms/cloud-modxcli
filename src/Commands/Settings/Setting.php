<?php

namespace MODX\CloudCLI\Commands\Settings;

use MODX\CloudCLI\Commands\Command;
use Symfony\Component\Console\Output\OutputInterface;

class Setting extends Command
{
    public function __invoke(
        OutputInterface
        $output,
        $verbose = false,
        $key = null,
        $value = null,
        $namespace = 'core',
        $area = 'default',
        $new = false
    ): void
    {
        $setting = $this->modx->getObject('modSystemSetting', ['key' => $key]);
        if (empty($setting)) {
            if (!$new) {
                $output->writeln("Setting not found.");
                return;
            }
            $setting = $this->modx->newObject('modSystemSetting');
            $setting->set('key', $key);
            $setting->set('namespace', $namespace);
            $setting->set('area', $area);
        }
        $setting->set('value', $value);
        if ($setting->save()) {
            if ($verbose) {
                $output->writeln("Setting saved.");
            }
        } else {
            $output->writeln("Setting could not be saved.");
        }
    }
}
