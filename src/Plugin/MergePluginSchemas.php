<?php

namespace AKlump\HtaccessManager\Plugin;

use AKlump\HtaccessManager\JsonSchemaMerge\MergeSchemas;

class MergePluginSchemas {

  private array $plugins;

  public function __construct(array $plugins) {
    $this->plugins = $plugins;
  }

  public function __invoke(string $schema_id, array $schema): array {
    $plugin_schemas = [];
    foreach ($this->plugins as $plugin) {
      if (empty($plugin['config_schema'])) {
        continue;
      }
      $plugin_schemas[] = json_decode(file_get_contents($plugin['config_schema']), TRUE);
    }

    return (new MergeSchemas())($schema, ... $plugin_schemas);
  }
}
