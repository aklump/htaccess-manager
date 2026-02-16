<?php

namespace AKlump\HtaccessManager\Plugin;

use AKlump\HtaccessManager\Config\Defaults;
use AKlump\HtaccessManager\Exception\ConfigurationException;
use Symfony\Component\Filesystem\Path;

class RedirectsPlugin implements PluginInterface {

  const ERROR_HANDLER_PREFIX = '_handle-';

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

    // Don’t wrap that RedirectMatch block in <IfModule mod_alias.c>. Reason:
    // these rules are security-ish (blocking access to sensitive-ish paths). If
    // mod_alias were missing and you wrapped them, Apache would just skip the
    // whole block and you’d silently lose protection. Failing loudly is better.
    $this->fWritePluginStart();
    $error_handlers_by_code = $this->getErrorHandlers($output_file_config, $context);
    foreach ($redirects_by_code as $redirects) {
      foreach ($redirects as $redirect) {
        if (!empty($redirect[2])) {
          $pattern = (new PreparePattern())($redirect[1], new RedirectMatch());
          $this->fWriteLine('RedirectMatch %d %s %s', $redirect[0], $pattern, $redirect[2]);
        }
        else {
          if (!isset($error_handlers_by_code[$redirect[0]])) {
            $pattern = (new PreparePattern())($redirect[1], new RedirectMatch());
            $this->fWriteLine('RedirectMatch %d %s', $redirect[0], $pattern);
          }
          else {
            $error_handler = self::ERROR_HANDLER_PREFIX . $redirect[0] . '.php';
            (new WriteErrorHandler())($redirect[0], $error_handlers_by_code[$redirect[0]] . DIRECTORY_SEPARATOR . $error_handler);
            $pattern = (new PreparePattern())($redirect[1], new RewriteRule());
            $this->fWriteLine('RewriteRule %s %s [L]', $pattern, $error_handler);
          }
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

  private function getErrorHandlers(array $output_file_config, array $context): array {
    $error_handlers = $output_file_config['redirects']['error_handlers'] ?? [];
    $global = $context['config']['redirects']['error_handlers'] ?? [];
    if ($global) {
      $inherit_global = TRUE === ($output_file_config['redirects']['inherit'] ?? TRUE);
      if ($inherit_global) {
        foreach ($global as $webroot => $status_codes) {
          $error_handlers[$webroot] = $status_codes;
        }
      }
    }
    if (empty($error_handlers)) {
      return [];
    }
    $webroot = $output_file_config['webroot'] ?? NULL;
    if (empty($webroot)) {
      throw new ConfigurationException(sprintf('files.%s.webroot must be set in order to use error handlers.', $context['output_file_id'] ?? Defaults::OUTPUT_FILE_ID));
    }
    $webroot = Path::makeAbsolute($webroot, dirname($context['config_path']));
    if (!file_exists($webroot)) {
      throw new ConfigurationException(sprintf('files.%s.webroot must be a valid directory.', $context['output_file_id'] ?? Defaults::OUTPUT_FILE_ID));
    }

    $by_code = [];
    foreach ($error_handlers as $status_code) {
      $by_code[$status_code] = $webroot;
    }

    return $by_code;
  }

  private function onlyRedirects(array $redirect_groups): array {
    return array_filter($redirect_groups, function ($key) {
      return is_numeric($key);
    }, ARRAY_FILTER_USE_KEY);
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
