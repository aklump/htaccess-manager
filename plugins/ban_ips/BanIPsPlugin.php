<?php

namespace AKlump\HtaccessManager\Plugin;

class BanIPs implements PluginInterface {

  use PluginTrait;

  /**
   * @inheritDoc
   */
  public function getName(): string {
    return "Ban IPs";
  }

  public static function getPriority(): int {
    return 30;
  }

  /**
   * @inheritDoc
   */
  public function __invoke($output_file_resource, array $output_file_config, array &$context = []): void {
    $ips = [];
    $inherit_global = $context['config']['ban_ips_inherit'] ?? TRUE;
    if ($inherit_global) {
      $ips = $context['config']['ban_ips'] ?? [];
    }
    if (!empty($output_file_config['ban_ips'])) {
      $ips = array_merge($output_file_config['ban_ips'], $ips);
    }
    if (empty($ips)) {
      return;
    }
    $this->resource = $output_file_resource;
    $this->listAddItem("Using plugin: ban_ips");
    $ips = array_unique($ips);

    $this->fWritePluginStart();
    foreach ($ips as $ip) {
      $this->fWriteLine("deny from $ip", $ip);
    }
    $this->fWritePluginStop();
  }

}
