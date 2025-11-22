<?php

namespace AKlump\HtaccessManager\secure_config;

use AKlump\HtaccessManager\Plugin\PluginInterface;
use AKlump\HtaccessManager\Plugin\PluginTrait;

class SecureConfig implements PluginInterface {

  use PluginTrait;

  public function getName(): string {
    return 'Secure common configuration paths.';
  }

  public static function getPriority(): int {
    return 30;
  }

  public function __invoke($output_file_resource, array $output_file_config, array &$context = []): void {
    if (empty($output_file_config['secure_config'])) {
      return;
    }
    $this->resource = $output_file_resource;
    $this->fWritePluginStart();
    // No <IfModule> wrapper is used because these security rules rely on
    // mod_alias, which is always loaded on any correctly configured Apache
    // installation. Wrapping them would only hide misconfiguration: if
    // mod_alias were missing, the rules would silently fail instead of
    // producing an error. For security- critical blocks, failing loudly is
    // safer and ensures predictable behavior.
    $this->fWriteLine('RedirectMatch 403 ^/.*\.env[^/]*$');
    $this->fWriteLine('RedirectMatch 403 ^/.*\b(phpinfo|php_info|info)\b\.php$');
    $this->fWriteLine('RedirectMatch 403 ^/.*\.?(services|config|settings)(\.[^/]+)?\.(ya?ml|config|ini)$');
    $this->fWriteLine('RedirectMatch 403 ^/.*\b(composer\.json|package\.json|composer\.lock|package-lock\.json|yarn\.lock)$');
    $this->fWritePluginStop();
  }
}
