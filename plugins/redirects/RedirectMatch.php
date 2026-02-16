<?php

namespace AKlump\HtaccessManager\Plugin;

class RedirectMatch implements PathProcessorInterface {

  use PathProcessorTrait;

  public function __invoke(string $path): string {
    $delimiter = $this->getDelimiter($path);

    // Remove any leading characters that should be ignored when looking for
    // leading slash.
    $temp = ltrim($path, $delimiter . '^');

    if (substr($temp, 0, 1) !== '/') {
      $temp2 = '/' . $temp;
      // Use leading / in patterns for RedirectMatch.
      $path = str_replace($temp, $temp2, $path);
    }

    return $path;
  }

}
