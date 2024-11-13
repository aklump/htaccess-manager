<?php

namespace AKlump\HtaccessManager\Plugin;

use Composer\Autoload\ClassLoader;

/**
 * @code
 * $class_loader = require __DIR__ . '/vendor/autoload.php';
 *
 * $plugins = (new \AKlump\HtaccessManager\Plugin\GetPlugins(
 * [__DIR__ . '/plugins/'],
 * [\AKlump\HtaccessManager\Plugin\PluginInterface::class],
 * '*.schema.json',
 * $class_loader
 * ))();
 * @endcode
 */
class GetPlugins {

  private array $directories;

  private array $interfaces;

  private string $configSchemaPattern;

  private ClassLoader $classLoader;

  /**
   * @var mixed
   */
  private $sortFunction;

  public function __construct(
    array $plugin_directories,
    array $plugin_interfaces,
    $sort_function,
    string $config_json_schema_file_pattern,
    ClassLoader $class_loader
  ) {
    $this->directories = $plugin_directories;
    $this->interfaces = $plugin_interfaces;
    $this->sortFunction = $sort_function;
    $this->configSchemaPattern = $config_json_schema_file_pattern;
    $this->classLoader = $class_loader;
  }

  public
  function __invoke(): array {
    $plugins = [];
    foreach ($this->directories as $directory) {
      $plugins = array_merge($plugins, $this->scanForPlugins($directory));
    }

    // Add autoloading for the plugin classes.
    foreach ($plugins as $plugin) {
      $this->classLoader->addPsr4('AKlump\HtaccessManager\Plugin\\', dirname($plugin['class_path']));
      //      $this->classLoader->addPsr4('AKlump\HtaccessManager\Tests\Unit\Plugin\\', dirname($plugin['class_path']));
    }

    uasort($plugins, $this->sortFunction);

    return $plugins;
  }

  private
  function isClassAPlugin(
    string $class
  ): bool {
    foreach ($this->interfaces as $interface) {
      if (isset(class_implements($class)[$interface])) {
        return TRUE;
      }
    }

    return FALSE;
  }

  private
  function scanForPlugins(
    $start_dir, &$context = []
  ) {
    $php_files = glob($start_dir . '/*.php');
    if (empty($php_files)) {
      $directories = glob($start_dir . '/*', GLOB_ONLYDIR);
      foreach ($directories as $directory) {
        $this->scanForPlugins($directory, $context);
      }
    }
    else {
      $context['declared_classes'] = get_declared_classes();
      foreach ($php_files as $filename) {
        require_once $filename;
        $declared_classes = get_declared_classes();
        $new_classes = array_diff($declared_classes, $context['declared_classes']);
        $context['declared_classes'] = $declared_classes;
        foreach ($new_classes as $new_class) {
          if (!$this->isClassAPlugin($new_class)) {
            continue;
          }
          $schema_files = glob(dirname($filename) . '/' . $this->configSchemaPattern);
          $config_schema = array_values($schema_files)[0] ?? NULL;
          if ($config_schema) {
            $config_schema = realpath($config_schema);
          }
          $context['plugins'][] = [
            'class_path' => realpath($filename),
            'classname' => $new_class,
            'config_schema' => $config_schema,
          ];
        }
      }
    }

    return $context['plugins'] ?? [];
  }

}
