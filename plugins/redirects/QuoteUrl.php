<?php

namespace AKlump\HtaccessManager\redirects;

class QuoteUrl {

  public function __invoke(string $url): string {
    if (strstr($url, '%20')) {
      $url = str_replace('%20', ' ', $url);
      $url = '"' . $url . '"';
    }

    return $url;
  }
}
