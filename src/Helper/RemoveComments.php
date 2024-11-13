<?php

namespace AKlump\HtaccessManager\Helper;

class RemoveComments {

  public function __invoke(string $content): string {
    // This pattern removes lines that start with '#' and optionally have
    // leading whitespace
    $content = preg_replace('/^\s*#.*$/m', '', $content);

    return preg_replace('/^\R/m', '', $content);
  }
}
