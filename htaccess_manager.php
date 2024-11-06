#!/usr/bin/env php
<?php
// htaccess_manager.php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use AKlump\HtaccessManager\BuildCommand;

$application = new Application();
$application->setName('Htaccess Manager');
$application->setVersion('0.0.0');
$application->add(new BuildCommand());
$application->run();
