<?php

namespace MODX\CloudCLI\Commands\Plugins;

use MODX\CloudCLI\Commands\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;

class GetList extends Command
{
    public function __invoke(
        OutputInterface $output,
        $verbose = false,
        $showInactive = false,
        $sort = 'id',
        $limit = 20,
        $offset = 0
    ): void
    {
        if (!in_array($sort, ['id', 'name'])) {
            $output->writeln("Invalid sort option.");
            return;
        }
        if ($verbose) {
            $output->writeln("Checking for installed plugins.");
        }
        $c = $this->modx->newQuery('modPlugin');
        if (!$showInactive) {
            $c->where(array('disabled'=>0));
        }
        if ($sort == 'id') {
            $c->sortby('id', 'ASC');
        } else {
            $c->sortby('name', 'ASC');
        }
        $count = $this->modx->getCount('modPlugin', $c);
        $c->limit($limit, $offset);
        $plugins = $this->modx->getCollection('modPlugin', $c);
        $table = new Table($output);
        $header = [
            'ID',
            'Name'
        ];
        if ($verbose) {
            $header[] = 'Category';
            $header[] = 'Events';
        } else {
            $table->setStyle('compact');
        }
        if ($showInactive) {
            $header[] = 'Disabled';
        }
        $table->setHeaders($header);
        $idx = 0;
        /** @var \MODX\Revolution\modPlugin $plugin */
        foreach ($plugins as $plugin) {
            $idx++;
            $row = [
                $plugin->get('id'),
                $plugin->get('name'),
            ];
            if ($verbose) {
                $category = $plugin->getOne('Category');
                $row[] = $category ? $category->get('category') : '';
                $events = $plugin->getMany('PluginEvents');
                $eventNames = array();
                foreach ($events as $event) {
                    $eventNames[] = $event->get('event');
                }
                $row[] = implode("\n", $eventNames);
            }
            if ($showInactive) {
                $row[] = $plugin->get('disabled') ? 'Yes' : 'No';
            }
            $table->addRow($row);
            if ($verbose && $idx < $count && $idx < $limit) {
                $table->addRow(new TableSeparator());
            }
        }

        if ($count == 0) {
            $output->writeln("No Plugins Found.");
        } else {
            $table->render();
            $start = $offset + 1;
            $through = $offset + $limit;
            if ($verbose) {
                if ($count > $limit || $through > $limit) {
                    $output->writeln("Showing $start through $through");
                }
                $activeText = $showInactive ? 'Total'  :'Active';
                $output->writeln("$activeText Plugins: ".$count);
            } elseif ($count > $limit || $through > $limit) {
                $output->writeln("$start-$through of $count");
            }
        }
    }
}