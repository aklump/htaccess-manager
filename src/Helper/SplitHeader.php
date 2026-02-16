<?php

namespace AKlump\HtaccessManager\Helper;

use RuntimeException;

class SplitHeader {

  public function __invoke(string $content): array {
    preg_match('/^#.+?\n\n/s', $content, $matches);
    $body = substr($content, strlen($matches[0] ?? ''));
    if (empty($body)) {
      throw new RuntimeException('Empty body');
    }
    $header = rtrim($matches[0]) . PHP_EOL . PHP_EOL;

    return [$header, $body];
  }

}
