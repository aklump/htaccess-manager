<?php

namespace AKlump\HtaccessManager\Plugin;

use AKlump\HtaccessManager\Exception\PluginFailedException;

/**
 * Merge in partials from files or URLs.
 */
class SourcePlugin implements PluginInterface {

  use PluginTrait;

  public function getName(): string {
    return 'Source';
  }

  public function __invoke($output_file_resource, array $output_file_config): void {
    $this->tryValidateStreamResource($output_file_resource);
    if (empty($output_file_config['source'])) {
      return;
    }
    $this->listAddItem('Using plugin: ' . $this->getName());
    foreach ($output_file_config['source'] as $partial) {
      $this->handleSingleSource($output_file_resource, $partial);
    }
  }

  private function handleSingleSource($output_resource, $partial) {
    if (preg_match('#^http#i', $partial)) {
      $this->writeFileHeader($output_resource, [
        'Downloaded from ' . $partial,
      ]);
      $this->listAddItem("Downloading $partial");
      $content = $this->getRemoteFileContent($partial);
    }
    elseif (is_file($partial)) {
      $this->writeFileHeader($output_resource, [
        'Copied from ' . $partial,
      ]);
      $this->listAddItem("Importing $partial");
      $content = file_get_contents($partial);
    }
    else {
      throw new PluginFailedException($this, "$partial cannot be located as configured.  Is this a file? Does it exist? Is the URL correct?");
    }
    fwrite($output_resource, rtrim($content, PHP_EOL) . PHP_EOL);
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
