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
    if (empty($output_file_config['www_prefix']) || !in_array($output_file_config['www_prefix'], [
        'add',
        'remove',
      ])) {
      return;
    }
    $this->resource = $output_file_resource;

    $force_ssl = $context['config']['force_ssl'] ?? $output_file_config['force_ssl'] ?? FALSE;
    $this->protossl = '%{ENV:protossl}';
    if ($force_ssl) {
      $this->protossl = 's';
    }

    $this->listAddItem('Using plugin: ' . $this->getName());
    $this->fWritePluginStart();
    // Open the declaration...
    $this->fWriteLine('<IfModule mod_rewrite.c>');
    $this->fWriteLine('  RewriteEngine on');
    $this->fWriteLine('  RewriteCond %{HTTP_HOST} .');

    switch ($output_file_config['www_prefix']) {
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
