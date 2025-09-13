#!/usr/bin/env php
<?php
// SPDX-License-Identifier: BSD-3-Clause


use AKlump\HtaccessManager\BuildCommand;
use AKlump\HtaccessManager\Plugin\PluginInterface;
use AKlump\PluginFramework\GetPlugins;
use Symfony\Component\Console\Application;

// https://getcomposer.org/doc/articles/vendor-binaries.md#finding-the-composer-autoloader-from-a-binary
if (isset($GLOBALS['_composer_autoload_path'])) {
  // As of Composer 2.2...
  $_composer_autoload_path = $GLOBALS['_composer_autoload_path'];
}
else {
  // < Composer 2.2
  foreach ([
             __DIR__ . '/../../autoload.php',
             __DIR__ . '/../vendor/autoload.php',
             __DIR__ . '/vendor/autoload.php',
           ] as $_composer_autoload_path) {
    if (file_exists($_composer_autoload_path)) {
      break;
    }
  }
}
$class_loader = require_once $_composer_autoload_path;

if (!class_exists('\Symfony\Component\Filesystem\Path')) {
  class_alias('\AKlump\HtaccessManager\Filesystem\Path', '\Symfony\Component\Filesystem\Path');
}

$plugins = (new GetPlugins(
  [
    __DIR__ . '/src/Plugin',
    __DIR__ . '/plugins/',
  ],
  [PluginInterface::class],
  function ($a, $b) {
    $a = (new $a['classname']())->getPriority();
    $b = (new $b['classname']())->getPriority();

    return $b - $a;
  },
  '*.schema.json',
  $class_loader
))();

$application = new Application();
$application->setName('Htaccess Manager');
$application->setVersion('0.0.6');
$application->add(new BuildCommand(__FILE__, $plugins));
$application->run();
