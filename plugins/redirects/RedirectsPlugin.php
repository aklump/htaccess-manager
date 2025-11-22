<?php

namespace AKlump\HtaccessManager\redirects;

use AKlump\HtaccessManager\Config\Defaults;
use AKlump\HtaccessManager\Exception\ConfigurationException;
use AKlump\HtaccessManager\Plugin\PluginInterface;
use AKlump\HtaccessManager\Plugin\PluginTrait;
use Symfony\Component\Filesystem\Path;

class RedirectsPlugin implements PluginInterface {

  use PluginTrait;

  /**
   * @inheritDoc
   */
  public function getName(): string {
    return 'Redirects';
  }

  /**
   * @inheritDoc
   */
  public static function getPriority(): int {
    return 20;
  }

  /**
   * @inheritDoc
   */
  public function __invoke($output_file_resource, array $output_file_config, array &$context = []): void {
    $redirects_by_code = $this->getRedirects($output_file_config, $context);
    if (empty($redirects_by_code)) {
      return;
    }
    $this->resource = $output_file_resource;

    $this->fWritePluginStart();
    foreach ($redirects_by_code as $redirects) {
      foreach ($redirects as $redirect) {
        if (!empty($redirect[2])) {
          $this->fWriteLine('RedirectMatch %d %s %s', $redirect[0], $this->wrapFromUrlWithMatchingPattern($redirect[1]), $redirect[2]);
        }
        else {
          $this->fWriteLine('RedirectMatch %d %s', $redirect[0], $this->wrapFromUrlWithMatchingPattern($redirect[1]));
        }
      }
    }
    $this->fWritePluginStop();
  }

  private function getRedirects(array $output_file_config, array $context): array {
    $global_config = $context['config'] ?? [];
    $redirect_groups = $this->onlyRedirects($output_file_config["redirects"] ?? []);
    $global = $this->onlyRedirects($global_config['redirects'] ?? []);
    if ($global) {
      $inherit_global = TRUE === ($output_file_config['redirects']['inherit'] ?? TRUE);
      if ($inherit_global) {
        foreach ($global as $code => $global_redirect) {
          $redirect_groups[$code] = $redirect_groups[$code] ?? [];
          $redirect_groups[$code] = array_merge($redirect_groups[$code], $global_redirect);
        }
      }
    }

    foreach ($redirect_groups as $code => &$redirects) {
      $redirects = array_unique($redirects);
      $redirects = array_map(function ($redirect) use ($code, $context) {
        $result = explode(' ', $redirect, 2);

        if (isset($result[1])) {
          $this->lintRedirectTarget($result[1], $code, $context);
        }

        array_unshift($result, $code);

        return $result;
      }, $redirects);
    }
    unset($redirects);

    return $redirect_groups;
  }

  private function onlyRedirects(array $redirect_groups): array {
    return array_filter($redirect_groups, function ($key) {
      return is_numeric($key);
    }, ARRAY_FILTER_USE_KEY);
  }

  private function wrapFromUrlWithMatchingPattern(string $from): string {
    $first_char = substr($from, 0, 1);
    $last_char = substr($from, -1);
    if ($first_char === $last_char && ($first_char === '@' || $first_char === '#')) {
      $from = substr($from, 1, -1);

      return (new QuoteUrl())($from);
    }

    // Use pathinfo to determine if $from is a file
    if ($this->isFile($from)) {
      // It's a file, anchor regex without trailing slash
      return (new QuoteUrl())("^$from\$");
    }

    // No extension, treat as directory-like, allow optional trailing slash
    return (new QuoteUrl())("^$from/?\$");
  }

  private function isFile(string $value): bool {
    $extension = Path::getExtension($value);
    if (empty($extension)) {
      return FALSE;
    }

    return preg_match('/[a-z]/i', $extension);
  }

  /**
   * Catch syntax errors in the redirect target.
   *
   * @param string $redirect_target
   *
   * @return void
   *
   * @throws \AKlump\HtaccessManager\Exception\ConfigurationException If the syntax is wrong.
   */
  private function lintRedirectTarget(string $redirect_target, int $redirect_code, $context): void {
    if (preg_match('#\\\(\$\d)#', $redirect_target, $matches)) {
      throw new ConfigurationException(sprintf('In "%s" there is a %d redirect target "%s" that appears to be escaping a regexp capture group; you should remove the backslash, e.g, "%s".', $context['output_file_id'] ?? Defaults::OUTPUT_FILE_ID, $redirect_code, $redirect_target, str_replace($matches[0], $matches[1], $redirect_target)));
    }
  }

}
