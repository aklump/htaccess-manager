<?php

namespace AKlump\HtaccessManager\Plugin;

class PreparePattern {

  use PathProcessorTrait;

  public function __invoke(string $pattern, PathProcessorInterface $processor): string {
    $pattern = (new AutoRegex())($pattern);
    $pattern = $processor($pattern);
    if ($delimiter = $this->getDelimiter($pattern)) {
      $pattern = trim($pattern, $delimiter);
    }

    return (new HandleQuotations())($pattern);
  }
}
