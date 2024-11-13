<?php

namespace AKlump\HtaccessManager\Plugin;

/**
 * Add hotlink denial code.
 */
class HotlinksPlugin implements PluginInterface {

  use PluginTrait;
  use SSLTrait;

  /**
   * @inheritDoc
   */
  public function getName(): string {
    return 'Hotlinks';
  }

  public static function getPriority(): int {
    return 20;
  }

  /**
   * @inheritDoc
   */
  public function __invoke($output_file_resource, array $output_file_config, array &$context = []): void {
    if (empty($output_file_config['hotlinks']['deny'])) {
      return;
    }

    $this->resource = $output_file_resource;
    $this->listAddItem(sprintf("Using plugin: %s", $this->getName()));


    # Forbid linking to assets in the site, a.k.a. "hotlinking".
    $this->fWritePluginStart();
    $this->fWriteLine("<IfModule mod_rewrite.c>");
    $this->fWriteLine("  RewriteEngine on");
    $this->fWriteLine("  RewriteCond %{HTTP_REFERER} !^$");
    foreach ($output_file_config['valid_hosts'] as $host) {
      $domain = parse_url($host, PHP_URL_HOST);
      $this->fWriteLine("  RewriteCond %%{HTTP_HOST} !^%s\$ [NC]", $domain);
      $this->fWriteLine("  RewriteCond %%{HTTP_REFERER} !^%s(?:$|/) [NC]", $host);
    }
    $this->fWriteLine("  RewriteRule .(%s)\$ - [F,NC]", implode('|', $output_file_config['hotlinks']['deny']));
    $this->fWriteLine("</IfModule>");
    $this->fWritePluginStop();
  }

}
