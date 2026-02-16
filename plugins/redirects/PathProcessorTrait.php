<?php

namespace AKlump\HtaccessManager\Plugin;

trait PathProcessorTrait {

  private function isRegexExpression(string $value): bool {
    $delimitter = $this->getDelimiter($value);

    return $delimitter === '#' || $delimitter === '@';
  }

  private function getDelimiter(string $value): string {
    if (substr($value, 0, 1) === substr($value, -1)) {
      return substr($value, 0, 1);
    }

    return '';
  }
}
