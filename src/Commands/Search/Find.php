<?php

namespace MODX\CloudCLI\Commands\Search;

use MODX\CloudCLI\Commands\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Find extends Command
{
    private $maxLength = 100;
    public function __invoke(InputInterface $input, OutputInterface $output): void
    {
        $string = $input->getArgument('query');
        $searchResources = $input->getOption('resources');
        $searchChunks = $input->getOption('chunks');
        $searchSnippets = $input->getOption('snippets');
        $searchTemplates = $input->getOption('templates');
        $searchPlugins = $input->getOption('plugins');

        if ($this->checkString($string)) {
            $output->writeln("String is not valid.");
            return;
        }

        if ($searchResources)
        {
            $this->searchResources($input, $output);
        }

        if ($searchChunks)
        {
            $this->searchChunks($input, $output);
        }
    }

    public function searchResources(InputInterface $input, OutputInterface $output): void
    {
        $class = 'modResource';
        if (empty($this->modx->map['modResource'])) {
            if (empty($this->modx->map['MODX\\Revolution\\modResource'])) {
                $output->writeln("Class modResource not found.");
                return;
            }
            $class = 'MODX\\Revolution\\modResource';
        }
        $string = $input->getArgument('query');
        $limit = $input->getOption('limit') ?? 20;
        $offset = $input->getOption('offset') ?? 0;
        $verbose = $input->getOption('verbose');
        $fields = $input->getOption('resource-fields') ??
            'pagetitle,longtitle,description,introtext,content';
        $fields = explode(',', $fields);
        $this->maxLength = 100 / count($fields);
        if (in_array('id', $fields)) {
            $key = array_search('id', $fields);
            unset($fields[$key]);
        }
        if ($this->checkString($string)) {
            $output->writeln("String is not valid.");
            return;
        }
        if ($verbose) {
            $output->writeln("Searching Resources for string: ".$string);
            $output->writeln("Fields: ".implode(', ', $fields));
        }
        $c = $this->modx->newQuery($class);
        $skipped = [];
        foreach ($fields as $field) {
            if (
                !isset($this->modx->map[$class]['fieldMeta'][$field])
                || !in_array($this->modx->map[$class]['fieldMeta'][$field]['phptype'], ['string', 'json'])
            ) {
                $skipped[] = $field;
                continue;
            }
            $c->where(['OR:'.$field.':LIKE' => '%'.$string.'%']);
        }
        if ($verbose && !empty($skipped))
        {
            $output->writeln("Skipped non string fields: ".implode(', ', $skipped));
        }
        $total = $this->modx->getCount($class, $c);
        $c->limit($limit, $offset);
        $resources = $this->modx->getCollection($class, $c);

        $table = new Table($output);

        $header = ['ID'];
        foreach ($fields as $field) {
            $header[] = $field;
        }

        $table->setHeaders($header);
        foreach ($resources as $resource) {
            $row = [$resource->get('id')];
            foreach ($fields as $field) {
                $row[] = $this->formatField($resource->get($field), $string);
            }
            $table->addRow($row);
        }
        $table->render();
        if ($total == 0 ) {
            $output->writeln("No Resources Found.");
        }
        if ($verbose && $total > 0) {
            $output->writeln("Total Matching Resources: ".$total);
        } else if ($total > $limit) {
            $output->writeln("$offset-$limit of $total");
        }
    }

    public function searchChunks(InputInterface $input, OutputInterface $output): void
    {
        $class = 'modChunk';
        if (empty($this->modx->map['modChunk'])) {
            if (empty($this->modx->map['MODX\\Revolution\\modChunk'])) {
                $output->writeln("Class modChunk not found.");
                return;
            }
            $class = 'MODX\\Revolution\\modChunk';
        }
        $string = $input->getArgument('query');
        $limit = $input->getOption('limit') ?? 20;
        $offset = $input->getOption('offset') ?? 0;
        $verbose = $input->getOption('verbose');
        $fields = $input->getOption('chunk-fields') ??
            'name,snippet';
        $fields = explode(',', $fields);
        $this->maxLength = 100 / count($fields);
        if (in_array('id', $fields)) {
            $key = array_search('id', $fields);
            unset($fields[$key]);
        }
        if ($this->checkString($string)) {
            $output->writeln("String is not valid.");
            return;
        }
        if ($verbose) {
            $output->writeln("Searching Chunks for string: ".$string);
            $output->writeln("Fields: ".implode(', ', $fields));
        }
        $c = $this->modx->newQuery($class);
        $skipped = [];
        foreach ($fields as $field) {
            if (
                !isset($this->modx->map[$class]['fieldMeta'][$field])
                || !in_array($this->modx->map[$class]['fieldMeta'][$field]['phptype'], ['string', 'json'])
            ) {
                $skipped[] = $field;
                continue;
            }
            $c->where(['OR:'.$field.':LIKE' => '%'.$string.'%']);
        }
        if ($verbose && !empty($skipped))
        {
            $output->writeln("Skipped non string fields: ".implode(', ', $skipped));
        }
        $total = $this->modx->getCount($class, $c);
        $c->limit($limit, $offset);
        $resources = $this->modx->getCollection($class, $c);

        $table = new Table($output);

        $header = ['ID'];
        foreach ($fields as $field) {
            $header[] = $field;
        }

        $table->setHeaders($header);
        foreach ($resources as $resource) {
            $row = [$resource->get('id')];
            foreach ($fields as $field) {
                $row[] = $this->formatField($resource->get($field), $string);
            }
            $table->addRow($row);
        }
        $table->render();
        if ($total == 0 ) {
            $output->writeln("No Chunks Found.");
        }
        if ($verbose && $total > 0) {
            $output->writeln("Total Matching Chunks: ".$total);
        } else if ($total > $limit) {
            $output->writeln("$offset-$limit of $total");
        }
    }

    private function checkString($string): bool
    {
        return (empty($string) || !is_string($string));
    }

    private function formatField($field, $string)
    {
        if (is_array($field)) {
            $field = json_encode($field);
        }
        if (!is_string($field)) {
            $field = print_r(
                $field,
                true
            );
        }
        $content = strip_tags($field);
        $content = str_replace(["\r","\n"], ' ', $content);
        $content = preg_replace("/\s+/", ' ', $content);
        if (str_contains($content, $string))
        {
            // Get the section of content with the string
            $start = strpos($content, $string);
            if ($start < ($this->maxLength / 2)) {
                $start = 0;
            } else {
                $start = $start - ($this->maxLength / 2);
            }
        } else {
            $start = 0;
        }
        return substr($content, $start, $this->maxLength);
    }
}