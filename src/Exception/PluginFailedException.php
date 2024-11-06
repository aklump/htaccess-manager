<?php

namespace AKlump\HtaccessManager\Exception;

class PluginFailedException extends \RuntimeException {

  public function __construct(
    \AKlump\HtaccessManager\Plugin\PluginInterface $plugin_instance,
    $message = "",
    $code = 0,
    $previous = NULL
  ) {
    $message = sprintf('%s plugin failed: %s', $plugin_instance->getName(), $message);
    parent::__construct($message, $code, $previous);
  }
}
