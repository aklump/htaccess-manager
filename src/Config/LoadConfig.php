<?php

namespace AKlump\HtaccessManager\Config;

use InvalidArgumentException;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Yaml\Yaml;

class LoadConfig {

  public function __invoke(string $config_path): array {
    if (!Path::isAbsolute($config_path)) {
      throw new InvalidArgumentException(sprintf('$config_path must be absolute, not: %s', $config_path));

    }
    if (!file_exists($config_path)) {
      throw new InvalidArgumentException(sprintf('Missing configuration file: %s', $config_path));
    }
    $config = Yaml::parseFile($config_path);

    return $this->makePathsAbsolute($config, $config_path);
  }

  private function makePathsAbsolute($config, string $config_path) {
    $config_dir = dirname($config_path);
    foreach ($config['files'] as &$file) {
      foreach ($file['source'] as &$path) {
        if (!Path::isAbsolute($path) && strpos($path, 'http') !== 0) {
          $path = Path::makeAbsolute($path, $config_dir);
        }
        unset($path);
      }
      unset($file);
    }

    return $config;
  }
}
