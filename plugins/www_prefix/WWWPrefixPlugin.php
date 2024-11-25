<?php

namespace AKlump\HtaccessManager\Plugin;

/**
 * Add or remove the 'www.' prefix from the domain name.
 *
 * In some cases handle force SSL as well.
 */
class WWWPrefixPlugin implements PluginInterface {

  use PluginTrait;
  use SSLTrait;

  private string $protossl;

  public static function willHandleForceSSL(array $output_file_config): bool {
    return in_array(static::getWWWPrefixValue($output_file_config), [
      'add',
      'remove',
    ]);
  }

  /**
   * @inheritDoc
   */
  public function getName(): string {
    return 'WWW Prefix';
  }

  public static function getPriority(): int {
    return 10;
  }

  /**
   * @inheritDoc
   */
  public function __invoke($output_file_resource, array $output_file_config, array &$context = []): void {
    $www_prefix = $this->getWWWPrefixValue($output_file_config);
    if (!in_array($www_prefix, ['add', 'remove'])) {
      return;
    }
    $this->resource = $output_file_resource;

    $force_ssl = FALSE;
    $this->protossl = '%{ENV:protossl}';

    if (self::willHandleForceSSL($output_file_config)) {
      $force_ssl = $this->getForceSSLConfigValue($output_file_config);
      if ($force_ssl) {
        $this->protossl = 's';
      }
    }

    $this->listAddItem('Using plugin: ' . $this->getName());
    $this->fWritePluginStart();
    // Open the declaration...
    $this->fWriteLine('<IfModule mod_rewrite.c>');
    $this->fWriteLine('  RewriteEngine on');

    if (!$force_ssl) {
      $this->fWriteLine('  # Used to set the appropriate http/https protocol in the rewrite.');
      $this->fWriteLine("  RewriteCond %{HTTPS} on");
      $this->fWriteLine("  RewriteRule ^ - [E=protossl:s]");
    }

    switch ($www_prefix) {
      case 'add':
        $this->addPrefix();
        break;
      case 'remove':
        $this->removePrefix();
        break;
    }

    if (TRUE === $force_ssl) {
      $this->forceSSL();
    }

    // ... Close the direction.
    $this->fWriteLine("</IfModule>");
    $this->fWritePluginStop();
  }

  private function addPrefix() {
    $this->fWriteLine("  # Ensure the domain has the leading \"www.\" prefix");
    $this->fWriteLine("  RewriteCond %{HTTP_HOST} !^www\. [NC]");
    $this->fWriteLine("  RewriteRule ^ http{$this->protossl}://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]");
  }

  private function removePrefix() {
    $this->fWriteLine("  # Remove the leading \"www.\" prefix");
    $this->fWriteLine("  RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]");
    $this->fWriteLine("  RewriteRule ^ http{$this->protossl}://%1%{REQUEST_URI} [L,R=301]");
  }

}
