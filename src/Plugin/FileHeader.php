<?php

namespace AKlump\HtaccessManager\Plugin;

use AKlump\HtaccessManager\Config\Defaults;
use DateTimeInterface;

class FileHeader implements PluginInterface {

  use PluginTrait;

  /**
   * @inheritDoc
   */
  public function getName(): string {
    return 'File Header';
  }

  /**
   * @inheritDoc
   */
  public static function getPriority(): int {
    return 100;
  }

  /**
   * @inheritDoc
   */
  public function __invoke($output_file_resource, array $output_file_config, array &$context = []): void {
    $this->resource = $output_file_resource;
    $this->fWriteHeader([
      $output_file_config['title'] ?? Defaults::OUTPUT_FILE_TITLE,
    ]);
    if (!isset($context['config']['header']) || $context['config']['header'] !== '') {
      $this->fWriteLine('# ' . strtoupper($context['config']['header'] ?? Defaults::OUTPUT_FILE_HEADER));
    }
    $this->fWriteLine('# (Built on: %s)', date_create()->format(DateTimeInterface::ATOM));
    if (!empty($context['file_header']['@see'])) {
      $this->fWriteLine('# @see %s', $context['file_header']['@see']);
    }
    $this->fWriteLine('');
  }
}
