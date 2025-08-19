<?php

use Composer\InstalledVersions;
use DI\ContainerBuilder;
use Silly\Application;

require dirname(__DIR__) . '/vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();

InstalledVersions::getRootPackage();

$app = new Application('cloudcli', InstalledVersions::getPrettyVersion('modx/cloudcli'));

$app->useContainer($container, true, true);

$app->command('config [--core-path=] [--config-key=]',
    \MODX\CloudCLI\Commands\Config::class,
    ['c']
)->descriptions('Configure connection to MODX');

$app->command('extras:upgrade [--clean]',
    \MODX\CloudCLI\Commands\Extras\Upgrade::class,
    ['eU']
)->descriptions('Upgrades all extras to the latest version.');

$app->command('extras:clean',
    \MODX\CloudCLI\Commands\Extras\Clean::class,
    ['eC']
)->descriptions('Cleans all extras that are not installed.');

$app->command('extras:list [--updates-only] [--limit=] [--offset=]',
    \MODX\CloudCLI\Commands\Extras\GetList::class,
    ['eL']
)->defaults(['limit' => 20, 'offset' => 0])
    ->descriptions('List all installed extras.');

$app->command('plugins:list [--show-inactive] [--sort=] [--limit=] [--offset=]',
    \MODX\CloudCLI\Commands\Plugins\GetList::class,
    ['pL']
)->defaults(['sort' => 'name', 'limit' => 20, 'offset' => 0])
    ->descriptions('List plugins');

$app->command('plugins:disable [name-or-id]',
    [\MODX\CloudCLI\Commands\Plugins\Plugin::class, 'disable'],
    ['pD']
)->descriptions('Disable a plugin');

$app->command('plugins:enable [name-or-id]',
    [\MODX\CloudCLI\Commands\Plugins\Plugin::class, 'enable'],
    ['pE']
)->descriptions('Enable a plugin');

$app->command('refresh',
    \MODX\CloudCLI\Commands\Refresh::class,
    ['r']
)->descriptions('Refresh the MODX cache');

$app->command('settings:list [key] [--namespace=] [--area=] [--limit=] [--offset=]',
    \MODX\CloudCLI\Commands\Settings\GetList::class,
    ['sL']
)->defaults(['limit' => 20, 'offset' => 0])
    ->descriptions('List all system settings');

$app->command('settings:set [key] [value] [--namespace=] [--area=] [--new]',
    \MODX\CloudCLI\Commands\Settings\Setting::class,
    ['sS']
)->defaults(['namespace' => 'core', 'area' => 'default'])
    ->descriptions('Set the value of a system setting');

$app->command('users:list [--active-only] [--sort=] [--username=] [--limit=] [--offset=]',
    \MODX\CloudCLI\Commands\Users\GetList::class,
    ['uL']
)->defaults(['sort' => 'username', 'limit' => 20, 'offset' => 0])
    ->descriptions('Refresh the MODX cache');

$app->command('users:activate [name-or-id]',
    [\MODX\CloudCLI\Commands\Users\User::class, 'activate'],
    ['uA']
)->descriptions('Activate a user');

$app->command('users:deactivate [name-or-id]',
    [\MODX\CloudCLI\Commands\Users\User::class, 'deactivate'],
    ['uD']
)->descriptions('Deactivate a user');

$app->command('users:block [name-or-id]',
    [\MODX\CloudCLI\Commands\Users\User::class, 'block'],
    ['uB']
)->descriptions('Block a user');

$app->command('users:unblock [name-or-id]',
    [\MODX\CloudCLI\Commands\Users\User::class, 'unblock'],
    ['uU']
)->descriptions('Unblock a user');

$app->command('users:password [name-or-id] [password] [--reset]',
    [\MODX\CloudCLI\Commands\Users\User::class, 'password'],
    ['uP']
)->descriptions('Change a user\'s password');

$app->command('users:create [--email=] [--username=] [--password=]',
    [\MODX\CloudCLI\Commands\Users\User::class, 'create'],
    ['uC']
)->descriptions('Create a new admin user');

try {
    $app->setDefaultCommand('list');
    $app->run();
} catch (\Exception $e) {
    echo $e->getMessage();
}