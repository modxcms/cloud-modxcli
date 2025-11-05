<?php

namespace MODX\CloudCLI\Commands\Search;

use MODX\CloudCLI\Commands\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Replace extends Command
{
    private $maxLength = 100;
    public function __invoke(InputInterface $input, OutputInterface $output): void
    {

        $string = $input->getArgument('query');

        $replace = $input->getOption('replace');

        if ($this->checkString($string) || $this->checkString($replace)) {
            $output->writeln("String is not valid.");
            return;
        }

        if ($input->getOption('resources'))
        {
            $this->replaceResources($input, $output);
        }

        if ($input->getOption('chunks'))
        {
            $this->replaceChunks($input, $output);
        }

        if ($input->getOption('snippets'))
        {
            $this->replaceSnippets($input, $output);
        }

        if ($input->getOption('templates'))
        {
            $this->replaceTemplates($input, $output);
        }

        if ($input->getOption('plugins'))
        {
            $this->replacePlugins($input, $output);
        }

        if ($input->getOption('tvs'))
        {
            $this->replaceTVs($input, $output);
        }
    }

    public function replaceResources(InputInterface $input, OutputInterface $output): void
    {
        $class = 'modResource';
        $fields = $input->getOption('resource-fields') ??
            'pagetitle,longtitle,description,introtext,content';
        $this->replace($input, $output, $class, $fields);
    }

    public function replaceChunks(InputInterface $input, OutputInterface $output): void
    {
        $class = 'modChunk';
        $fields = $input->getOption('chunk-fields') ??
            'name,snippet';
        $this->replace($input, $output, $class, $fields);
    }

    public function replaceTemplates(InputInterface $input, OutputInterface $output): void
    {
        $class = 'modTemplate';
        $fields = $input->getOption('template-fields') ??
            'templatename,content';
        $this->replace($input, $output, $class, $fields);
    }

    public function replaceSnippets(InputInterface $input, OutputInterface $output): void
    {
        $class = 'modSnippet';
        $fields = $input->getOption('snippet-fields') ??
            'name,snippet';
        $this->replace($input, $output, $class, $fields);
    }

    public function replacePlugins(InputInterface $input, OutputInterface $output): void
    {
        $class = 'modPlugin';
        $fields = $input->getOption('plugin-fields') ??
            'name,plugincode';
        $this->replace($input, $output, $class, $fields);
    }

    public function replaceTVs(InputInterface $input, OutputInterface $output): void
    {
        $class = 'modTemplateVar';
        $fields = $input->getOption('tv-fields') ??
            'name,type,input_properties,output_properties,elements,default_text';
        $this->replace($input, $output, $class, $fields);
    }

    private function replace(InputInterface $input, OutputInterface $output, string $class, string $fields): void
    {
        $classFormatted = $class;
        if (empty($this->modx->map[$class])) {
            if (empty($this->modx->map['MODX\\Revolution\\'.$class])) {
                $output->writeln("Class $class not found.");
                return;
            }
            $class = 'MODX\\Revolution\\'.$class;
        }
        $string = $input->getArgument('query');
        $replace = $input->getOption('replace');
        $verbose = $input->getOption('verbose');
        $regex = $input->getOption('regex');
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
            $output->writeln("Searching $classFormatted for string: $string");
            $output->writeln("Replacing fields: ".implode(', ', $fields));
        }
        $c = [];
        $set = [];
        $skipped = [];
        $regex_replace = preg_replace('/(\\\)/', '\\\\\\\\', $replace);
        foreach ($fields as $field) {
            if (
                !isset($this->modx->map[$class]['fieldMeta'][$field])
                || !in_array($this->modx->map[$class]['fieldMeta'][$field]['phptype'], ['string', 'json'])
            ) {
                $skipped[] = $field;
                continue;
            }
            $c['OR:'.$field.':LIKE'] = '%'.$string.'%';
            if (!empty($regex)) {
                $set[$field] = "REGEXP_REPLACE($field, '$regex', '$regex_replace')";
            } else {
                $set[$field] = "REPLACE($field, '$string', '$replace')";
            }
        }
        if ($verbose && !empty($skipped))
        {
            $output->writeln("Skipped non string fields: ".implode(', ', $skipped));
        }
        $total = $this->modx->getCount($class, $c);
        $updated = $this->modx->updateCollection($class, $set, $c);

        if ($total == 0 ) {
            $output->writeln("No $classFormatted Found.");
        }
        if ($verbose && $total > 0) {
            $output->writeln("Total Matching $classFormatted(s): $total");
            $output->writeln("Total Updated $classFormatted(s): $updated");
        } elseif ($updated > 0) {
            $output->writeln("Updated $updated $classFormatted(s)");
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