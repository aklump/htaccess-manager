<?php

namespace AKlump\HtaccessManager\redirects;

use AKlump\HtaccessManager\Plugin\PluginInterface;
use AKlump\HtaccessManager\Plugin\PluginTrait;

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
    return 10;
  }

  /**
   * @inheritDoc
   */
  public function __invoke($output_file_resource, array $output_file_config, array &$context = []): void {
    $redirects_by_code = $this->getRedirects($output_file_config, $context['config'] ?? []);
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

  private function getRedirects(array $output_file_config, array $global_config): array {
    $redirect_groups = $this->onlyRedirects($output_file_config["redirects"] ?? []);

    $global = $this->onlyRedirects($global_config['redirects'] ?? []);
    if ($global) {
      $inherit_glboal = isset($output_file_config['redirects']['inherit']) && TRUE === $output_file_config['redirects']['inherit'];
      if ($inherit_glboal) {
        foreach ($global as $code => $global_redirect) {
          $redirect_groups[$code] = $redirect_groups[$code] ?? [];
          $redirect_groups[$code] = array_merge($redirect_groups[$code], $global_redirect);
        }
      }
    }

    foreach ($redirect_groups as $code => &$redirects) {
      $redirects = array_unique($redirects);
      $quote_url = new QuoteUrl();
      $redirects = array_map(function ($redirect) use ($code, $quote_url) {
        $result = explode(' ', $redirect, 2);
        $result = array_map($quote_url, $result);
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
    return "^$from/?\$";
  }

}
