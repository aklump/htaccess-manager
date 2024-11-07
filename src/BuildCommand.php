<?php

namespace AKlump\HtaccessManager;

use AKlump\HtaccessManager\Config\LoadConfig;
use AKlump\HtaccessManager\Helper\GetShortPath;
use AKlump\HtaccessManager\Helper\PrepareOutputPath;
use AKlump\HtaccessManager\Output\Icons;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;

class BuildCommand extends \Symfony\Component\Console\Command\Command {

  protected static $defaultName = 'build';

  protected static $defaultDescription = 'Lorem ipsum build.';

  protected function configure() {
    $this->addArgument('configuration', InputArgument::REQUIRED);
  }


  protected function execute(InputInterface $input, OutputInterface $output) {

    $short_path = new GetShortPath(getcwd());
    $prepare_path = new PrepareOutputPath();

    $config_path = $input->getArgument('configuration');
    $config_path = Path::makeAbsolute($config_path, getcwd());
    $config = (new LoadConfig())($config_path);

    foreach ($config['files'] as $output_file_config) {

      $output_file_path = array_shift($output_file_config['output']);
      $prepare_path($output_file_path);

      $output_file_resource = fopen($output_file_path, 'w');
      $this->applyPlugins($output_file_resource, $output_file_config);
      $output->writeln(Icons::FILE . $short_path($output_file_path));
      fclose($output_file_resource);

      // Sometimes there are more than one output files, if so copy the
      // newly-created output file to the other ones.
      foreach ($output_file_config['output'] as $target_file_path) {
        $prepare_path($target_file_path);
        copy($output_file_path, $target_file_path);
        $output->writeln(Icons::FILE . $short_path($target_file_path));
      }
    }

    // @url <https://symfony.com/doc/5.x/console.html>
    return \Symfony\Component\Console\Command\Command::SUCCESS;
    //    return \Symfony\Component\Console\Command\Command::INVALID;
    //    return \Symfony\Component\Console\Command\Command::FAILURE;
  }

  /**
   * @param resource $output_file_resource
   * @param array $output_file_config
   *
   * @return void
   */
  private function applyPlugins($output_file_resource, array $output_file_config) {
    (new \AKlump\HtaccessManager\Plugin\SourcePlugin())($output_file_resource, $output_file_config);
  }


}
