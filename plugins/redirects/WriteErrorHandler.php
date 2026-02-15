<?php

namespace AKlump\HtaccessManager\redirects;

use Symfony\Component\HttpFoundation\Response;

class WriteErrorHandler {

  public function __invoke(int $status, string $filepath) {
    $message = Response::$statusTexts[$status] ?? 'Unknown Error';
    $parent_dir = dirname($filepath);
    $this->ensureDirectoryExists($parent_dir);
    $php_script = <<<PHP
        <?php
        declare(strict_types=1);
        
        http_response_code($status);
        header('Content-Type: text/plain; charset=UTF-8');
        header('Cache-Control: max-age=600, public');
        header('X-Robots-Tag: noindex, nofollow');
        
        echo '$message' . PHP_EOL;
        exit;
        PHP;
    if (file_put_contents($filepath, $php_script) === FALSE) {
      throw new \RuntimeException(sprintf('Failed to write error handler file: %s', $filepath));
    }
  }

  private function ensureDirectoryExists(string $directory): void {
    if (is_dir($directory)) {
      return;
    }

    if (!mkdir($directory, 0755, TRUE) && !is_dir($directory)) {
      throw new \RuntimeException(sprintf('Failed to create directory: %s', $directory));
    }
  }
}
