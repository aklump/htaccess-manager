<?php

namespace AKlump\HtaccessManager\Plugin;

use Symfony\Component\Filesystem\Path;

// TODO This is here due to a bug in the plugin autoloading.  Remove once fixed.
require_once __DIR__ . '/PathProcessorInterface.php';
require_once __DIR__ . '/PathProcessorTrait.php';

/**
 * Class AutoRegex
 *
 * Processes a given path and returns a regular expression representation of it.
 * The class determines whether the path is already a regex, a file path, or a
 * directory-like path and constructs the appropriate regex pattern accordingly.
 */
class AutoRegex implements PathProcessorInterface {

  use PathProcessorTrait;

  public function __invoke(string $path): string {
    if ($this->isRegexExpression($path)) {
      return $path;
    }

    // Replace unquoted '.' with '\.' to prevent it from matching any character.
    $path = preg_replace('#([a-z0-9])\.#i', '$1\.', $path);

    if ($this->isFile($path)) {
      // It's a file, anchor regex without trailing slash
      return "#^$path\$#";
    }

    // No extension, treat as directory-like, allow optional trailing slash
    return "#^$path/?\$#";
  }

  private function isFile(string $value): bool {
    $extension = Path::getExtension($value);
    if (empty($extension)) {
      return FALSE;
    }

    return preg_match('/[a-z]/i', $extension);
  }
}
