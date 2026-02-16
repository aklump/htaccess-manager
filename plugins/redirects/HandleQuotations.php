<?php

namespace AKlump\HtaccessManager\Plugin;

class HandleQuotations implements PathProcessorInterface {

  public function __invoke(string $path): string {
    if (strstr($path, '%20')) {
      $path = str_replace('%20', ' ', $path);
      $path = '"' . $path . '"';
    }

    return $path;
  }
}
