<?php

namespace AKlump\PluginFramework;

use Composer\Autoload\ClassLoader;
use ReflectionClass;

/**
 * @code
 * $class_loader = require __DIR__ . '/vendor/autoload.php';
 *
 * $plugins = (new \AKlump\PluginFramework\GetPlugins(
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

  public function __invoke(): array {
    $plugins = [];
    foreach ($this->directories as $directory) {
      $plugins = array_merge($plugins, $this->scanForPlugins($directory));
    }

    // Add autoloading for the plugin classes.
    foreach ($plugins as $plugin) {
      $this->classLoader->addPsr4($plugin['namespace'] . '\\', dirname($plugin['class_path']));
    }

    uasort($plugins, $this->sortFunction);

    return $plugins;
  }

  private function isClassAPlugin(string $class): bool {
    foreach ($this->interfaces as $interface) {
      if (isset(class_implements($class)[$interface])) {
        return TRUE;
      }
    }

    return FALSE;
  }

  private function scanForPlugins($start_dir, &$context = []) {
    $php_files = glob($start_dir . '/*.php');
    if (empty($php_files)) {
      $directories = glob($start_dir . '/*', GLOB_ONLYDIR);
      foreach ($directories as $directory) {
        $this->scanForPlugins($directory, $context);
      }
    }
    else {
      $context = $this->loadPluginClasses($php_files, $context);
    }

    return $context['plugins'] ?? [];
  }

  private function getConfigSchema(string $filename): ?string {
    $schema_files = glob(dirname($filename) . '/' . $this->configSchemaPattern);

    return array_values($schema_files)[0] ?? NULL;
  }

  private function loadPluginClasses(array $php_files, $context) {
    $context['declared_classes'] = get_declared_classes();
    foreach ($php_files as $filename) {
      require_once $filename;
      $new_classes = array_diff(get_declared_classes(), $context['declared_classes']);
      $context['declared_classes'] = get_declared_classes();
      foreach ($new_classes as $new_class) {
        if (!$this->isClassAPlugin($new_class)) {
          continue;
        }
        $config_schema = $this->getConfigSchema($filename);
        if ($config_schema) {
          $config_schema = realpath($config_schema);
        }
        $context['plugins'][] = [
          'class_path' => realpath($filename),
          'namespace' => (new ReflectionClass($new_class))->getNamespaceName(),
          'classname' => $new_class,
          'config_schema' => $config_schema,
        ];
      }
    }

    return $context;
  }

}
