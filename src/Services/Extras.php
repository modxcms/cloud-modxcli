<?php

namespace MODX\CloudCLI\Services;

use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Output\OutputInterface;

class Extras
{
    /** @var \modX $this->modx */
    public $modx;

    public $packages = [];

    public $providers = [];

    private $output;

    private $progress;

    protected $total = 0;

    public function __construct($modx)
    {
        $this->modx = $modx;
    }

    public function get($updatesOnly = false, $limit = 0, $offset = 0)
    {
        $c = $this->modx->newQuery('transport.modTransportPackage');
        $c->where(array('installed:IS NOT'=>null));
        $c->sortby('package_name', 'ASC');
        $c->sortby('installed', 'ASC');
        $this->total = $updatesOnly ? 0 : $this->modx->getCount('transport.modTransportPackage', $c);
        $c->limit($limit, $offset);
        $packages = $this->modx->getCollection('transport.modTransportPackage', $c);
        foreach ($packages as $package) {
            if ($this->progress instanceof ProgressIndicator) {
                $this->progress->advance();
            }
            $hasUpdates = $this->checkUpdates($package);
            if ($updatesOnly && !$hasUpdates) {
                continue;
            } elseif ($updatesOnly) {
                $this->total++;
            }
            $package->set('has_updates', $hasUpdates);
            $this->packages[$package->get('package_name')] = $package;
        }
        return $this->packages;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function setProgress(ProgressIndicator $progress)
    {
        $this->progress = $progress;
    }

    public function checkUpdates($package)
    {
        $updates = false;
        if ($package->get('provider') > 0 && $this->modx->getOption('auto_check_pkg_updates',null,false))
        {
            $cacheKey = 'mgr/providers/updates/'.$package->get('provider').'/'.$package->get('signature').'.response';
            $cacheOpts = [
                \xPDO::OPT_CACHE_KEY => $this->modx->cacheManager->getOption('cache_packages_key', null, 'packages'),
                \xPDO::OPT_CACHE_HANDLER => $this->modx->cacheManager->getOption('cache_packages_handler', null, $this->modx->cacheManager->getOption(\xPDO::OPT_CACHE_HANDLER)),
            ];
            $cache = $this->modx->cacheManager->get($cacheKey, $cacheOpts);
            if (empty($cache)) {
                /** @var \MODX\Revolution\Transport\modTransportProvider $provider */
                if (!empty($this->providers[$package->get('provider')])) {
                    $provider = $this->providers[$package->get('provider')];
                } else {
                    $provider = $package->getOne('Provider');
                    if ($provider) {
                        $this->providers[$provider->get('id')] = $provider;
                    }
                }
                if (!empty($provider)) {
                    $cache = $provider->latest($package->get('signature'));
                    $this->modx->cacheManager->set($cacheKey, $cache, 3600, $cacheOpts);
                }
            }
            $updates = $cache;
        }
        return $updates;
    }

    public function installLatest($package, $signature = null, $location = null)
    {
        $provider = $package->getOne('Provider');
        if ($this->progress) {
            $this->progress->advance();
        }
        if ($provider) {
            /** @var \MODX\Revolution\Transport\modTransportPackage $newPackage */
            $newPackage = $provider->transfer($signature, null, [
                'location' => $location,
                'overwrite' => true,
            ]);
            if ($newPackage) {
                if (!$newPackage->install()){
                    throw new \Exception($this->modx->lexicon('package_install_err'));
                }
            }
            return $newPackage;
        } else {
            throw new \Exception($this->modx->lexicon('package_provider_err'));
        }
    }

    public function purge(): void
    {
        $packages = $this->get();
        foreach ($packages as $package) {
            $c = $this->modx->newQuery('transport.modTransportPackage', array(
                'package_name' => $package->get('package_name')
            ));
            $c->where(array('installed:!=' => '0000-00-00 00:00:00'));
            $c->sortby('installed', 'desc');
            $c->limit(1000, 1);
            $purgedPackages = $this->modx->getIterator('transport.modTransportPackage', $c);
            /** @var \MODX\Revolution\Transport\modTransportPackage $purgedPackage */
            foreach ($purgedPackages as $purgedPackage) {
                if ($this->output instanceof OutputInterface)
                {
                    $this->output->writeln('Purging: '.$purgedPackage->get('package_name'));
                }
                $this->removePackage($purgedPackage);
            }
        }
        $this->clearCache();
    }

    private function removePackage($package): void
    {
        $transportZip = $this->modx->getOption('core_path') . 'packages/' . $package->signature . '.transport.zip';
        $transportDir = $this->modx->getOption('core_path') . 'packages/' . $package->signature . '/';
        if (file_exists($transportZip) && file_exists($transportDir)) {
            /* remove transport package */
            if ($package->remove() == false) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, $this->modx->lexicon('package_err_remove', array('signature' => $package->getPrimaryKey())));
                $this->failure($this->modx->lexicon('package_err_remove', array('signature' => $package->getPrimaryKey())));
                return;
            }
        } else {
            /* for some reason the files were removed, so just remove the DB object instead */
            $package->remove();
        }

        $this->removeTransportZip($transportZip);
        $this->removeTransportDirectory($transportDir);

        $this->modx->invokeEvent('OnPackageRemove', array(
            'package' => $package
        ));
    }

    private function removeTransportZip($transportZip): void
    {
        if (file_exists($transportZip)) {
            @unlink($transportZip);
        } else {
            if ($this->output instanceof OutputInterface)
            {
                $this->output->writeln('Transport zip file not found: '.$transportZip);
            }
        }
    }

    private function removeTransportDirectory($transportDir): void
    {
        if (is_dir($transportDir)) {
            $this->modx->cacheManager->deleteTree($transportDir,true,false,array());
        } else {
            if ($this->output instanceof OutputInterface)
            {
                $this->output->writeln('Transport directory not found: '.$transportDir);
            }
        }
    }

    private function clearCache(): void
    {
        if ($this->output instanceof OutputInterface) {
            $this->output->writeln('Clearing cache...');
        }
        $this->modx->getCacheManager();
        $this->modx->cacheManager->refresh(array($this->modx->getOption('cache_packages_key', null, 'packages') => array()));
        $this->modx->cacheManager->refresh();
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}