#!/usr/bin/env php
<?php
$class_loader = require __DIR__ . '/vendor/autoload.php';

use AKlump\HtaccessManager\BuildCommand;
use Symfony\Component\Console\Application;

$plugins = (new \AKlump\PluginFramework\GetPlugins(
  [
    __DIR__ . '/src/Plugin',
    __DIR__ . '/plugins/',
  ],
  [\AKlump\HtaccessManager\Plugin\PluginInterface::class],
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
$application->setVersion('0.0.0');
$application->add(new BuildCommand(__FILE__, $plugins));
$application->run();
