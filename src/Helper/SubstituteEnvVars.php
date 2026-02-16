<?php

namespace AKlump\HtaccessManager\Helper;

class SubstituteEnvVars {

  /**
   * Substitutes environment variables in a configuration array.
   *
   * It recursively traverses the array and replaces any string starting with $
   * with the value of the corresponding environment variable.
   * It also handles environment variables in array keys.
   *
   * @param mixed $config The configuration data.
   *
   * @return mixed The configuration data with environment variables substituted.
   */
  public function __invoke($config) {
    if (is_string($config)) {
      return $this->substituteString($config);
    }
    if (is_array($config)) {
      $new_config = [];
      foreach ($config as $key => $value) {
        $new_key = is_string($key) ? $this->substituteString($key) : $key;
        $new_config[$new_key] = $this($value);
      }

      return $new_config;
    }

    return $config;
  }

  private function substituteString(string $value): string {
    return preg_replace_callback('/(?:\$\{([a-zA-Z_][a-zA-Z0-9_]*)\})|(?:\$([a-zA-Z_][a-zA-Z0-9_]*))/', function ($matches) {
      $env_var = !empty($matches[1]) ? $matches[1] : $matches[2];
      $env_val = getenv($env_var);

      return $env_val !== FALSE ? $env_val : $matches[0];
    }, $value);
  }
}
