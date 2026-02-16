<?php

namespace AKlump\HtaccessManager\Plugin;

interface PathProcessorInterface {

  public function __invoke(string $path): string;
}
