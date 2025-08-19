<?php

use MODX\CloudCLI\Compile;

require dirname(__DIR__) . '/vendor/autoload.php';

$compile = new Compile(dirname(__DIR__));
$compile->compile();