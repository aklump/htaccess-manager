<?php

namespace AKlump\HtaccessManager\Filesystem;

use Symfony\Component\Filesystem\Filesystem;

/**
 * This is a Shim so we can use Filesystem version 4.
 */
class Path {

  public static function isAbsolute($path_pattern) {
    return (new Filesystem())->isAbsolutePath($path_pattern);
  }

  public static function makeAbsolute($path, $base_path) {
    if (!(new Filesystem())->isAbsolutePath($path)) {
      $path = rtrim($base_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    if (file_exists($path)) {
      $path = realpath($path);
    }

    return $path;
  }

  public static function makeRelative($path, $base_path) {
    $filesystem = new Filesystem();
    if ($filesystem->isAbsolutePath($path)) {
      $is_file = is_file($path);
      $path = $filesystem->makePathRelative($path, $base_path);
      if ($is_file) {
        $path = rtrim($path, '/');
      }
    }

    return $path;
  }

}
