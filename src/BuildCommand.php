<?php

namespace AKlump\HtaccessManager;

use AKlump\HtaccessManager\Config\LoadConfig;
use Symfony\Component\Console\Input\InputArgument;

class BuildCommand extends \Symfony\Component\Console\Command\Command {

  protected static $defaultName = 'build';

  protected static $defaultDescription = 'Lorem ipsum build.';

  protected function configure() {
    $this->addArgument('configuration', InputArgument::REQUIRED);
  }


  protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output) {

    $config_path = $input->getArgument('configuration');
    if (!\Symfony\Component\Filesystem\Path::isAbsolute($config_path)) {
      $config_path = \Symfony\Component\Filesystem\Path::makeAbsolute($config_path, getcwd());
    }
    $config = (new LoadConfig())($config_path);

    // outputs a message followed by a "\n"
    $output->writeln('Build works!');

    // @url <https://symfony.com/doc/5.x/console.html>
    return \Symfony\Component\Console\Command\Command::SUCCESS;
    //    return \Symfony\Component\Console\Command\Command::INVALID;
    //    return \Symfony\Component\Console\Command\Command::FAILURE;
  }


}
