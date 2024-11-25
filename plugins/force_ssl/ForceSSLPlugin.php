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
    $force_ssl = $this->getForceSSLConfigValue($output_file_config);
    if (!$force_ssl) {
      return;
    }

    // Force SSL is true, but we may not need to handle it, if the other plugin
    // is doing so.
    if (WWWPrefixPlugin::willHandleForceSSL($output_file_config)) {
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
