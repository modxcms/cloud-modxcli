<?php

namespace MODX\CloudCLI\Commands\Settings;

use MODX\CloudCLI\Commands\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;

class GetList extends Command
{
    public function __invoke(
        OutputInterface $output,
        $verbose = false,
        $key = null,
        $namespace = null,
        $area = null,
        $limit = 20,
        $offset = 0
    ): void
    {
        if ($verbose) {
            $output->writeln("Getting settings.");
        }
        $c = $this->modx->newQuery('modSystemSetting');
        if ($key) {
            if ($verbose) {
                $output->writeln("Filtering by key: ".$key);
            }
            $c->where(['key:LIKE' => '%' . $key . '%']);
        }
        if ($area) {
            if ($verbose) {
                $output->writeln("Filtering by area: ".$area);
            }
            $c->where(['area' => $area]);
        }
        if ($namespace) {
            if ($verbose) {
                $output->writeln("Filtering by namespace: ".$namespace);
            }
            $c->where(['namespace' => $namespace]);
        }
        $count = $this->modx->getCount('modSystemSetting', $c);
        $c->limit($limit, $offset);
        $settings = $this->modx->getCollection('modSystemSetting', $c);
        $table = new Table($output);

        $headers = ['Key', 'Value'];
        if ($verbose) {
            $headers = array_merge($headers, [
                'Namespace',
                'Area',
            ]);
        }else {
            $table->setStyle('compact');
        }
        $table->setHeaders($headers);

        /** @var \MODX\Revolution\modSystemSetting $setting */
        $split = ($verbose) ? 60 : 90;
        $idx = 0;
        foreach ($settings as $setting) {
            $idx++;
            $value = str_split($setting->get('value'), $split);
            $row = [
                $setting->get('key'),
                implode("\n", $value),
            ];
            if ($verbose) {
                $row = array_merge($row, [
                    $setting->get('namespace'),
                    $setting->get('area'),
                ]);
            }
            $table->addRow($row);
            if ($verbose && $idx < $count && $idx < $limit) {
                $table->addRow(new TableSeparator());
            }
        }
        if ($count == 0) {
            $output->writeln("No Settings Found.");
        } else {
            $table->render();
            $start = $offset + 1;
            $through = $offset + $limit;
            if ($verbose) {
                if ($count > $limit || $through > $limit) {
                    $output->writeln("Showing $start through $through");
                }
                $output->writeln("Total Settings: ".$count);
            } elseif ($count > $limit || $through > $limit) {
                $output->writeln("$start-$through of $count");
            }
        }
    }

}