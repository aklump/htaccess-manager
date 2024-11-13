<?php

namespace AKlump\HtaccessManager\Plugin;

use AKlump\HtaccessManager\Exception\PluginFailedException;
use AKlump\HtaccessManager\Helper\GetShortPath;

/**
 * Merge in partials from files or URLs.
 */
class SourcePlugin implements PluginInterface {

  use PluginTrait;

  public function getName(): string {
    return 'Source';
  }

  public static function getPriority(): int {
    return 0;
  }

  public function __invoke($output_file_resource, array $output_file_config, array &$context = []): void {
    if (empty($output_file_config['source'])) {
      return;
    }
    $this->resource = $output_file_resource;
    $this->listAddItem('Using plugin: ' . $this->getName());
    $this->fWritePluginStart();
    foreach ($output_file_config['source'] as $index => $partial) {
      $debug_id = sprintf('%s.sources[%d]', $context['output_file_id'] ?? 'MISSING_ID', $index);
      $this->handleSingleSource($output_file_resource, $partial, $debug_id);
    }
    $this->fWritePluginStop();
  }

  private function handleSingleSource($output_resource, string $partial, string $debug_id): void {
    if (preg_match('#^http#i', $partial)) {
      $this->fWriteLine('# Downloaded from %s', $partial);

      $this->listAddItem("Downloading $partial");
      $content = $this->getRemoteFileContent($partial);
    }
    elseif (is_file($partial)) {
      $this->fWriteLine('# Copied from %s', $partial);
      $this->listAddItem("Importing $partial");
      $content = file_get_contents($partial);
    }
    else {
      throw new PluginFailedException($this, sprintf("$debug_id: $partial cannot be accessed.  Is this a file? Does it exist? Is the URL correct?", (new GetShortPath(getcwd()))($partial)));
    }
    fwrite($output_resource, rtrim($content, PHP_EOL) . PHP_EOL);
    $this->fWriteLine();
  }

  private function getRemoteFileContent($partial) {
    $ch = curl_init($partial);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $content = curl_exec($ch);
    curl_close($ch);

    return $content;
  }

}
