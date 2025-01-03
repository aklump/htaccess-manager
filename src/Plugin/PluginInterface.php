<?php

namespace AKlump\HtaccessManager\Plugin;

interface PluginInterface {

  /**
   * @return string The title-case plugin name.
   */
  public function getName(): string;

  /**
   * @return int The higher the earlier this plugin will be executed.
   */
  public static function getPriority(): int;

  /**
   * @param resource $output_file_resource The file stream resource being built.
   * @param array $output_file_config Configuration array for the given output
   * file resource only.
   * @param array &$context Can be used to share context between plugins.
   *
   * @return void
   */
  public function __invoke($output_file_resource, array $output_file_config, array &$context = []): void;

}
