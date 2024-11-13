<?php

namespace AKlump\HtaccessManager\Plugin;

use RuntimeException;

trait PluginTrait {

  /**
   * @var resource
   */
  protected $resource;

  /**
   * Writes a formatted header to a stream resource.
   *
   * @param resource $resource The file handle resource where the header will be written.
   * @param array $lines The lines to be encapsulated within the header.
   *
   * @return void
   */
  private function fWriteHeader(array $lines): void {
    $this->tryValidateResource();
    //    fwrite($this->resource, PHP_EOL);
    fwrite($this->resource, '#' . PHP_EOL);
    fwrite($this->resource, '#' . PHP_EOL);
    foreach ($lines as $line) {
      fwrite($this->resource, "# $line" . PHP_EOL);
    }
    fwrite($this->resource, '#' . PHP_EOL);
  }

  private function fWriteLine(string $line = '', ...$values): void {
    $this->tryValidateResource();
    if ($values) {
      fwrite($this->resource, sprintf($line, ...$values) . PHP_EOL);
    }
    else {
      fwrite($this->resource, $line . PHP_EOL);
    }
  }

  private function tryValidateResource(): void {
    if (!isset($this->resource)) {
      throw new RuntimeException(sprintf('$this->resource is not set in %s', __CLASS__));
    }
  }

  private function listAddItem(string $string) {
    //    echo $string . PHP_EOL;
  }

  private function fWritePluginStart() {
    $this->fWriteHeader([
      sprintf("%s Plugin", $this->getName()),
    ]);
  }

  private function fWritePluginStop() {
    $this->fWriteLine("# End %s plugin", $this->getName());
    $this->fWriteLine();
  }
}
