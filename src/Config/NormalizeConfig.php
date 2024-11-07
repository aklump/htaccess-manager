<?php

namespace AKlump\HtaccessManager\Config;

class NormalizeConfig {

  public function __invoke(array $config): array {
    $config['files'] = $config['files'] ?? [];
    foreach ($config['files'] as &$output_file_config) {
      $this->normalizeOne($output_file_config);
    }
    unset($output_file_config);

    return $config;
  }

  private function normalizeOne(array &$output_file_config) {
    if (!is_array($output_file_config['output'])) {
      $output_file_config['output'] = [$output_file_config['output']];
    }
    $output_file_config['output'] = array_values($output_file_config['output']);
  }


}
