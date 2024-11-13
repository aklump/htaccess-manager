<?php

namespace AKlump\HtaccessManager\Plugin;

class BanWordpressPlugin implements PluginInterface {

  use PluginTrait;

  /**
   * @inheritDoc
   */
  public function getName(): string {
    return "Ban Wordpress";
  }

  public static function getPriority(): int {
    return 0;
  }

  /**
   * @inheritDoc
   */
  public function __invoke($output_file_resource, array $output_file_config, array &$context = []): void {
    if (empty($output_file_config['ban_wordpress'])) {
      return;
    }
    $this->resource = $output_file_resource;
    $this->fWritePluginStart();
    $this->fWriteLine("<IfModule mod_rewrite.c>");
    $this->fWriteLine("  RewriteEngine on");
    $this->fWriteLine("  RewriteRule ^wp-login.php$ - [R=410,L]");
    $this->fWriteLine("</IfModule>");
    $this->fWritePluginStop();
  }
}
