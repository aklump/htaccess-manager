<?php

namespace AKlump\HtaccessManager\Plugin;

trait SSLTrait {

  private function getForceSSLConfigValue(array $output_file_config): bool {
    if (isset($output_file_config['force_ssl'])) {
      return (bool) $output_file_config['force_ssl'];
    }
    $schemes = [];
    if (!empty($output_file_config['valid_hosts'])) {
      foreach ($output_file_config['valid_hosts'] as $valid_host) {
        $schemes[] = parse_url($valid_host, PHP_URL_SCHEME);
      }
    }
    if (count(array_unique($schemes)) > 1) {
      return FALSE;
    }

    return in_array('https', $schemes);
  }

  /**
   * @param array $output_file_config
   *
   * @return string One of "add" (if all valid_hosts have www.), "remove" (if
   * all do not start with www.), or "default" if there is a mixture so as not
   * to be in agreement.
   */
  private static function getWWWPrefixValue(array $output_file_config): string {
    if (isset($output_file_config['www_prefix'])) {
      return $output_file_config['www_prefix'];
    }
    $has_www = 0;
    $default_value = 'default';
    if (empty($output_file_config['valid_hosts'])) {
      return $default_value;
    }
    // Count the number of hosts that begin with www.
    foreach ($output_file_config['valid_hosts'] as $valid_host) {
      if (preg_match('#://www\.#', $valid_host)) {
        ++$has_www;
      }
    }
    if ($has_www === count($output_file_config['valid_hosts'])) {
      return 'add';
    }
    elseif (0 === $has_www) {
      return 'remove';
    }
    else {
      return $default_value;
    }
  }

  private function forceSSL() {
    $this->fWriteLine("  # This line is required in some environments, e.g. Lando");
    $this->fWriteLine("  RewriteCond %{ENV:HTTPS} !^.*on");
    $this->fWriteLine("  # This line is more universal but doesn't always work.");
    $this->fWriteLine("  RewriteCond %{HTTPS} !^.*on");
    $this->fWriteLine("  RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]");
  }
}
