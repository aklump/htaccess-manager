<?php

namespace AKlump\HtaccessManager;

use AKlump\HtaccessManager\Config\LoadConfig;
use AKlump\HtaccessManager\Helper\GetShortPath;
use AKlump\HtaccessManager\Helper\PrepareOutputPath;
use AKlump\HtaccessManager\Helper\RemoveComments;
use AKlump\HtaccessManager\Helper\SplitHeader;
use AKlump\HtaccessManager\Output\Icons;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;

class BuildCommand extends Command {

  protected static $defaultName = 'build';

  protected static $defaultDescription = 'Command to process config and build htaccess files.';

  private string $pathToController;

  private array $plugins;

  public function __construct(string $path_to_controller, array $plugins) {
    parent::__construct();
    $this->pathToController = $path_to_controller;
    $this->plugins = $plugins;
  }

  protected function configure() {
    $this->addArgument('configuration', InputArgument::REQUIRED);
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $short_path = new GetShortPath(getcwd());
    $prepare_path = new PrepareOutputPath();

    $config_path = $input->getArgument('configuration');
    $config_path = Path::makeAbsolute($config_path, getcwd());
    $config = (new LoadConfig($this->plugins))($config_path);
    $context = [
      'config_path' => $config_path,
      'config' => $config,
      'file_header' => [
        '@see' => $this->pathToController,
      ],
    ];
    foreach ($config['files'] as $file_id => $output_file_config) {

      $output_filepath = array_shift($output_file_config['output']);
      $prepare_path($output_filepath);

      $output_file_resource = fopen($output_filepath, 'w');
      $context['output_filepath'] = $output_filepath;
      $context['output_file_id'] = $file_id;
      $this->applyPlugins($output_file_resource, $output_file_config, $context);
      $output->writeln(Icons::FILE . $short_path($output_filepath));
      fclose($output_file_resource);

      if ($output_file_config['remove_comments'] ?? FALSE) {
        $content = file_get_contents($output_filepath);
        try {
          list($header, $body) = (new SplitHeader())($content);
        }
        catch (RuntimeException $exception) {
          $output->writeln(sprintf('<error>Failed to remove comments from %s.</error>', $file_id));
          if (!defined('Command::FAILURE')) {
            return 1;
          }

          return Command::FAILURE;
        }
        file_put_contents($output_filepath, $header . (new RemoveComments())($body));
      }

      // Sometimes there are more than one output files, if so copy the
      // newly-created output file to the other ones.
      foreach ($output_file_config['output'] as $target_file_path) {
        $prepare_path($target_file_path);
        copy($output_filepath, $target_file_path);
        $output->writeln(Icons::FILE . $short_path($target_file_path));
      }
    }

    // @url <https://symfony.com/doc/5.x/console.html>
    if (!defined('Command::SUCCESS')) {
      return 0;
    }

    return Command::SUCCESS;
  }

  /**
   * @param resource $output_file_resource
   * @param array $output_file_config
   *
   * @return void
   */
  private function applyPlugins($output_file_resource, array $output_file_config, array $context = []) {
    foreach ($this->plugins as $plugin) {
      $instance = new $plugin['classname']();
      $instance($output_file_resource, $output_file_config, $context);
    }
  }

}
