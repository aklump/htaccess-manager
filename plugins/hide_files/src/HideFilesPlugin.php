<?php

namespace AKlump\HtaccessManager\hide_files;

use AKlump\HtaccessManager\Plugin\PluginInterface;
use AKlump\HtaccessManager\Plugin\PluginTrait;

/**
 * Use this to cause files to be forbidden whose basenames match a regex.
 *
 * For example you can hide all files named "todo.md".
 */
class HideFilesPlugin implements PluginInterface {

  use PluginTrait;

  public function getName(): string {
    return 'Hide Files';
  }

  public static function getPriority(): int {
    return 0;
  }

  public function __invoke($output_file_resource, array $output_file_config, array &$context = []): void {
    if (empty($output_file_config['hide_files'])) {
      return;
    }
    $this->resource = $output_file_resource;
    $this->fWritePluginStart();
    if (!empty($output_file_config['hide_files']['pattern'])) {
      $this->fWriteLine(sprintf("<FilesMatch \"%s\">", $output_file_config['hide_files']['pattern']));
    }
    else {
      throw new \RuntimeException('Only "pattern" is supported at this time');
    }
    $this->fWriteLine('  <IfModule mod_authz_core.c>');
    $this->fWriteLine('    Require all denied');
    $this->fWriteLine('  </IfModule>');
    $this->fWriteLine('  <IfModule !mod_authz_core.c>');
    $this->fWriteLine('    Order allow,deny');
    $this->fWriteLine('  </IfModule>');
    $this->fWriteLine('</FilesMatch>');
    $this->fWritePluginStop();
  }
}

# Protect files and directories from prying eyes.
