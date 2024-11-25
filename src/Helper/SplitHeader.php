<?php

namespace AKlump\HtaccessManager\Helper;

class SplitHeader {

  public function __invoke(string $content): array {
    preg_match('/^#.+\n\n/s', $content, $matches);
    $body = substr($content, strlen($matches[0]));
    $header = rtrim($matches[0]);

    return [$header, $body];
  }

}
