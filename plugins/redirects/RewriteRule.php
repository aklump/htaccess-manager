<?php

namespace AKlump\HtaccessManager\Plugin;

class RewriteRule implements PathProcessorInterface {

  use PathProcessorTrait;

  public function __invoke(string $path): string {
    $delimiter = $this->getDelimiter($path);

    // Remove any leading characters that should be ignored when looking for
    // leading slash.
    $temp = ltrim($path, $delimiter . '^');

    if ($temp !== ($temp2 = ltrim($temp, '/'))) {
      // Don’t use leading / in patterns for RewriteRule in .htaccess.
      $path = str_replace($temp, $temp2, $path);
    }

    return $path;
  }

}
