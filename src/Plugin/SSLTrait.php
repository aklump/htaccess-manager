<?php

namespace AKlump\HtaccessManager\Plugin;

trait SSLTrait {

  private function forceSSL() {
    $this->fWriteLine("  # This line is required in some environments, e.g. Lando");
    $this->fWriteLine("  RewriteCond %{ENV:HTTPS} !^.*on");
    $this->fWriteLine("  # This line is more universal but doesn't always work.");
    $this->fWriteLine("  RewriteCond %{HTTPS} !^.*on");
    $this->fWriteLine("  RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]");
  }
}
