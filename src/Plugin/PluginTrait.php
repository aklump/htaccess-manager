<?php

namespace AKlump\HtaccessManager\Plugin;

use InvalidArgumentException;

trait PluginTrait {

  /**
   * Throw if argument is not a stream resource.
   *
   * @param $resource
   *
   * @return void
   */
  private function tryValidateStreamResource($resource) {
    if (!is_resource($resource) || get_resource_type($resource) !== 'stream') {
      throw new InvalidArgumentException('Expected a file resource.');
    }
  }

  /**
   * Writes a formatted header to a stream resource.
   *
   * @param resource $resource The file handle resource where the header will be written.
   * @param array $lines The lines to be encapsulated within the header.
   *
   * @return void
   */
  private function writeFileHeader($resource, array $lines): void {
    fwrite($resource, PHP_EOL);
    fwrite($resource, '#' . PHP_EOL);
    fwrite($resource, '#' . PHP_EOL);
    foreach ($lines as $line) {
      fwrite($resource, "# $line" . PHP_EOL);
    }
    fwrite($resource, '#' . PHP_EOL);
  }

  private function listAddItem(string $string) {
    //    echo $string . PHP_EOL;
  }
}
