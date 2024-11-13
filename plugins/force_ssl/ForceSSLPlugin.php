<?php

namespace AKlump\HtaccessManager\Plugin;

/**
 * Force the site to be served using https.
 */
class ForceSSLPlugin implements PluginInterface {

  use PluginTrait;
  use SSLTrait;

  /**
   * @inheritDoc
   */
  public function getName(): string {
    return 'Force SSL';
  }

  public static function getPriority(): int {
    return 10;
  }

  /**
   * @inheritDoc
   */
  public function __invoke($output_file_resource, array $output_file_config, array &$context = []): void {
    if (empty($output_file_config['force_ssl'])) {
      return;
    }

    if (isset($output_file_config['www_prefix']) && in_array($output_file_config['www_prefix'], [
        'add',
        'remove',
      ])) {
      // Note: SSL is handled by the www_prefix plugin in this case.
      return;
    }

    $this->resource = $output_file_resource;
    $this->listAddItem("Using plugin: force_ssl");

    $this->fWritePluginStart();
    $this->fWriteLine("<IfModule mod_rewrite.c>");
    $this->fWriteLine("  RewriteEngine on");
    $this->forceSSL();
    $this->fWriteLine("</IfModule>");
    $this->fWritePluginStop();
  }

}
