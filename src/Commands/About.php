<?php

namespace MODX\CloudCLI\Commands;

use PDO;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class About extends Command
{
    public function __invoke(InputInterface $input, OutputInterface $output): void
    {
        $data = [];
        $verbose = $input->getOption('verbose');

        $this->modx->getVersionData();
        $serverOffset = (float)$this->modx->getOption('server_offset_time', null, 0) * 3600;

        /* general */
        $data['modx_version'] = $this->modx->version['full_appname'];
        $data['code_name'] = $this->modx->version['code_name'];
        $data['servertime'] = date('h:i:s A', time());
        $data['localtime'] = date('h:i:s A', time() + $serverOffset);
        $data['serveroffset'] = $serverOffset / (60 * 60);

        /* database info */
        $data['database_type'] = $this->modx->getOption('dbtype');
        $stmt = $this->modx->query('SELECT VERSION()');
        if ($stmt) {
            $result = $stmt->fetch(PDO::FETCH_COLUMN);
            $stmt->closeCursor();
        } else {
            $result = '-';
        }
        $data['database_version'] = $result;
        $data['database_charset'] = $this->modx->getOption('charset');
        $data['database_name'] = trim($this->modx->getOption('dbname'),
            $this->modx->_escapeCharOpen . $this->modx->_escapeCharClose);
        $data['database_server'] = $this->modx->getOption('host');
        $data['now'] = date('M d, Y h:i A', time());
        $data['table_prefix'] = $this->modx->getOption('table_prefix');
        $data['error_log'] = $this->getCache('error_log');
        if (empty($data['error_log']) && file_exists(MODX_CORE_PATH . 'cache/logs/error.log')) {
            $logSize = filesize(MODX_CORE_PATH . 'cache/logs/error.log');
            $data['error_log'] = $this->formatBytes($logSize);
            $this->setCache('error_log', $data['error_log']);
        } elseif (empty($data['error_log'])) {
            $data['error_log'] = '-';
        }
        $data['install_size'] = $this->formatBytes($this->getInstallSize());
        $data['active_users'] = $this->modx->getCount('modUser', ['active' => 1]);

        $table = new Table($output);

        $headers = ['Key', 'Value'];
        $table->setHeaders($headers);
        if (!$verbose)  {
            $table->setStyle('compact');
        }
        foreach ($data as $k => $v) {
            $table->addRow([$k, $v]);
        }
        $table->render();
    }

    private function getInstallSize(): int
    {
        $cache = $this->getCache('install_size');
        if ($cache) {
            return $cache;
        }
        $total = 0;
        if (!defined('MODX_BASE_PATH')) {
            define('MODX_BASE_PATH', '/www/');
        }
        $path = realpath(MODX_BASE_PATH);
        if($path !== false && $path !== '' && file_exists($path)){
            foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $object){
                $total += $object->getSize();
            }
        }
        $this->setCache('install_size', $total);
        return $total;
    }

    private function formatBytes(int $bytes): string
    {
        $sz = 'BKMGTP';
        $factor = (int) floor((strlen($bytes) - 1) / 3);
        if ($factor) {
            return sprintf("%.2f", $bytes / (1024 ** $factor)) . @$sz[$factor];
        }

        return $bytes.'B';
    }
}