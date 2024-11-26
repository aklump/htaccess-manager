#!/usr/bin/env php
<?php
// SPDX-License-Identifier: BSD-3-Clause

$class_loader = include $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';

use AKlump\HtaccessManager\BuildCommand;
use AKlump\HtaccessManager\Plugin\PluginInterface;
use AKlump\PluginFramework\GetPlugins;
use Symfony\Component\Console\Application;

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
$application->setVersion('0.0.1');
$application->add(new BuildCommand(__FILE__, $plugins));
$application->run();
