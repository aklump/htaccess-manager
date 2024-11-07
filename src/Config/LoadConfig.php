<?php

namespace AKlump\HtaccessManager\Config;

use AKlump\JsonSchema\JsonDecodeLossless;
use AKlump\JsonSchema\ValidateWithSchema;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LoadConfig
 *
 * This class is responsible for loading a configuration file in YAML format.
 * It validates the given configuration path for being absolute and ensures
 * the existence of the configuration file. Post validation, it parses the
 * YAML file and normalizes the configuration data.
 *
 * @throws InvalidArgumentException if $config_path is not absolute or the configuration file does not exist.
 */
class LoadConfig {

  public function __invoke(string $config_path): array {
    if (!Path::isAbsolute($config_path)) {
      throw new InvalidArgumentException(sprintf('$config_path must be absolute, not: %s', $config_path));
    }
    if (!file_exists($config_path)) {
      throw new InvalidArgumentException(sprintf('Missing configuration file: %s', $config_path));
    }
    $config = Yaml::parseFile($config_path);
    $config = (new NormalizeConfig())($config);
    $this->validateSchema($config);

    $config_dir = dirname($config_path);
    foreach ($config['files'] as &$output_file_config) {
      $this->makePathsAbsolute($output_file_config, $config_dir);
      unset($output_file_config);
    }

    return $config;
  }

  private function makePathsAbsolute(&$output_file_config, string $config_dir) {
    foreach ($output_file_config['output'] as &$path) {
      $path = Path::makeAbsolute($path, $config_dir);
    }
    unset($path);

    foreach ($output_file_config['source'] as &$path) {
      if (!Path::isAbsolute($path) && strpos($path, 'http') !== 0) {
        $path = Path::makeAbsolute($path, $config_dir);
      }
      unset($path);
    }
  }

  private function validateSchema(array $config) {
    $path_to_schema = __DIR__ . '/../../json_schema/user_config.schema.json';
    $schema_json = file_get_contents($path_to_schema);
    $validate = new ValidateWithSchema($schema_json, dirname($path_to_schema));

    $config_to_validate = (new JsonDecodeLossless())(json_encode($config));
    $errors = $validate($config_to_validate);
    $is_valid = empty($errors);
    if (!$is_valid) {
      $message = 'Invalid configuration:' . PHP_EOL;
      foreach ($errors as $erro) {
        $message .= $erro . PHP_EOL;
      }
      throw new RuntimeException($message);
    }
  }
}
