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
    return 20;
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
    $this->fWriteLine('<IfModule mod_alias.c>');
    // Use 404 for WordPress paths since they legitimately don't exist on this
    // non-Wordpress site. This provides the least information to potential
    // attackers while correctly indicating these resources were never here and
    // won't be coming back.
    $this->fWriteLine('  RedirectMatch 404 ^/wordpress');
    $this->fWriteLine('  RedirectMatch 404 ^/wp-(admin|includes|content)/.*$');
    $this->fWriteLine('  RedirectMatch 404 ^/wp-(config|login)\.php$');
    $this->fWriteLine('</IfModule>');
    $this->fWritePluginStop();
  }
}
