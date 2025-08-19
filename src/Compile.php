<?php

namespace MODX\CloudCLI;

use Symfony\Component\Finder\Finder;

class Compile
{
    /**
     * @var string The alias given to the phar that is created.
     */
    private $alias;
    /**
     * @var string The root path of the project being compiled.
     */
    private $path;

    public function __construct($path = '', $alias = 'modx.phar')
    {
        $this->path = $path;
         $this->alias = $alias;
    }

    public function compile()
    {
        $phar = new \Phar($this->alias, 0, $this->alias);
        $phar->setSignatureAlgorithm(\Phar::SHA1);
        $phar->startBuffering();

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->ignoreDotFiles(true)
            ->name('*.php')
            ->notName('Compile.php')
            ->in($this->path . '/src');
        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $dependencies = new Finder();
        $dependencies->files()
            ->ignoreVCS(true)
            ->ignoreDotFiles(true)
            ->name('*.php')
            ->in($this->path . '/vendor');
        foreach ($dependencies as $file) {
            $this->addFile($phar, $file);
        }

        $content = file_get_contents($this->path . '/bin/modx.php');
        $phar->addFromString('bin/modx', $content);

        $phar->setStub($this->getStub());
        $phar->stopBuffering();
    }

    /**
     * Get the stub code for the phar.
     *
     * @return string The stub PHP code.
     */
    private function getStub()
    {
        return <<<EOF
#!/usr/bin/env php
<?php
/**
 * This file is part of the modx package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Phar::mapPhar('{$this->alias}');
require 'phar://{$this->alias}/bin/modx';

__HALT_COMPILER();
EOF;
    }

    /**
     * Add a file to the Phar, optionally stripping whitespace.
     *
     * @param \Phar        $phar
     * @param \SplFileInfo $file
     * @param bool         $strip
     */
    private function addFile($phar, $file, $strip = true)
    {
        $path = str_replace($this->path . DIRECTORY_SEPARATOR, '', $file->getRealPath());

        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        } elseif ('LICENSE' === basename($file)) {
            $content = "\n" . $content . "\n";
        }

        $phar->addFromString($path, $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param  string $source A PHP string
     *
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }
}