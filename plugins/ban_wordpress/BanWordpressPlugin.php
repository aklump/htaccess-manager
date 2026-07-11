<?php

namespace AKlump\HtaccessManager\Plugin;

use AKlump\HtaccessManager\Config\Defaults;
use AKlump\HtaccessManager\Exception\ConfigurationException;
use Symfony\Component\Filesystem\Path;

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

    $error_handlers = $output_file_config['redirects']['error_handlers'] ?? [];
    $global = $context['config']['redirects']['error_handlers'] ?? [];
    if ($global) {
      $inherit_global = TRUE === ($output_file_config['redirects']['inherit'] ?? TRUE);
      if ($inherit_global) {
        $error_handlers = array_merge($error_handlers, $global);
      }
    }
    $use_error_handler = in_array(404, $error_handlers);

    $patterns = [
      '/wordpress',
      '/wp-(admin|includes|content)/.*',
      '/wp-(config|login)\.php',
    ];

    if ($use_error_handler) {
      $webroot = $output_file_config['webroot'] ?? NULL;
      if (empty($webroot)) {
        throw new ConfigurationException(sprintf('files.%s.webroot must be set in order to use error handlers.', $context['output_file_id'] ?? Defaults::OUTPUT_FILE_ID));
      }
      $webroot = Path::makeAbsolute($webroot, dirname($context['config_path']));
      if (!file_exists($webroot)) {
        throw new ConfigurationException(sprintf('files.%s.webroot must be a valid directory.', $context['output_file_id'] ?? Defaults::OUTPUT_FILE_ID));
      }

      $error_handler = '_handle-404.php';
      (new WriteErrorHandler())(404, $webroot . DIRECTORY_SEPARATOR . $error_handler);
      foreach ($patterns as $pattern) {
        $pattern = (new PreparePattern())($pattern, new RewriteRule());
        $this->fWriteLine('RewriteRule %s %s [L]', $pattern, $error_handler);
      }
    }
    else {
      $this->fWriteLine('<IfModule mod_alias.c>');
      foreach ($patterns as $pattern) {
        $pattern = (new PreparePattern())($pattern, new RedirectMatch());
        $this->fWriteLine('  RedirectMatch 404 %s', $pattern);
      }
      $this->fWriteLine('</IfModule>');
    }
    $this->fWritePluginStop();
  }
}
