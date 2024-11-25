<?php

namespace AKlump\HtaccessManager\Helper;

class PrepareOutputPath {

  /**
   * Ensures the directory of the given file path exists and is writable.
   *
   * @param string $output_filepath The path to the output file.
   *
   * @return void
   */
  public function __invoke(string $output_filepath): void {
    $basedir = dirname($output_filepath);
    if (!file_exists($basedir)) {
      mkdir($basedir, 0755, TRUE);
    }
    if (!is_writable($basedir)) {
      chmod($basedir, 0755);
    }
  }
}
