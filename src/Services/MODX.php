<?php

namespace MODX\CloudCLI\Services;

class MODX
{
    public $modx;

    public function __construct($load = true) {
        if (!$load) return;
        $this->load();
    }

    private function load()
    {
        $config = $this->getConfig();
        $corePath = $config['core_path'] ?? '';
        $configKey = $config['config_key'] ?? '';
        $tstart= microtime(true);

        if (file_exists($corePath . "config/" . $configKey . ".inc.php"))
        {
            define("MODX_CORE_PATH", $corePath);
            define("MODX_CONFIG_KEY", $configKey);
            if (!@include_once (MODX_CORE_PATH . "model/modx/modx.class.php")) {
                throw new \Exception("Unable to load MODX.");
            }
            ob_start();
            $this->modx = new \modX();
            $this->modx->startTime = $tstart;
            $this->modx->initialize();
        } else {
            throw new \Exception("MODX not configured correctly.");
        }
    }

    private function getConfig()
    {
        $home = $_SERVER['HOME'] ?? "/home/";
        $configPath = $home . "/.config/modx.json";
        if (file_exists($configPath)) {
            return json_decode(file_get_contents($configPath), true);
        } else {
            // Create a config file
            $config = [
                "core_path" => "/www/core/",
                "config_key" => "config",
            ];
            file_put_contents($configPath, json_encode($config));
            return $config;
        }
    }

    public function setConfig($key, $value)
    {
        $home = $_SERVER['HOME'] ?? "/home/";
        $configPath = $home . "/.config/modx.json";
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
        } else {
            // Create a config file
            $config = [
                "core_path" => "/www/core/",
                "config_key" => "config",
            ];
        }
        $config[$key] = $value;
        file_put_contents($configPath, json_encode($config));
    }
}