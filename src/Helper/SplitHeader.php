<?php

namespace AKlump\HtaccessManager\Helper;

class SplitHeader {

  public function __invoke(string $content): array {
    $result = preg_split('/#.+\n\n/', $content, 2);
    $result[0] = rtrim($result[0], PHP_EOL) . PHP_EOL . PHP_EOL;
    return $result;
  }

}
