<?php

namespace AKlump\HtaccessManager\Plugin;

/**
 * Add necessary http auth code.
 */
class HTTPAuthPlugin implements PluginInterface {

  use PluginTrait;

  /**
   * @inheritDoc
   */
  public function getName(): string {
    return 'HTTP Authentication';
  }

  public static function getPriority(): int {
    return 0;
  }

  /**
   * @inheritDoc
   */
  public function __invoke($output_file_resource, array $output_file_config, array &$context = []): void {
    if (empty($output_file_config['http_auth'])) {
      return;
    }
    $config = $output_file_config['http_auth'];
    $this->resource = $output_file_resource;

    $this->listAddItem("Using plugin: http_auth");
    $this->fWritePluginStart();

    $this->fWriteLine("AuthName \"%s\"", $config['title']);
    $this->fWriteLine("AuthUserFile \"%s\"", $config['user_file']);
    $this->fWriteLine("<IfModule mod_authz_groupfile.c>");
    $this->fWriteLine("  AuthGroupFile /dev/null");
    $this->fWriteLine("</IfModule>");
    $this->fWriteLine("AuthType Basic");
    $this->fWriteLine("Require valid-user");

    if (!empty($config['whitelist'])) {
      $this->fWriteLine("Order deny,allow");
      $this->fWriteLine("Deny from all");
      foreach ($config['whitelist'] as $ip) {
        $this->fWriteLine("Allow from $ip");
      }
      $this->fWriteLine("Satisfy any");
    }
    $this->fWritePluginStop();
  }
}
