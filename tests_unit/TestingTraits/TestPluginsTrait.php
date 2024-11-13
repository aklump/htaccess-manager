<?php

namespace AKlump\HtaccessManager\Tests\Unit\TestingTraits;

use ReflectionClass;

trait TestPluginsTrait {

  use TestWithFilesTrait;

  protected function getResourcePath(): string {
    $id = strtolower((new ReflectionClass($this))->getShortName());
    $id = preg_replace('#plugintest$#i', '', $id);

    return $this->getTestFileFilepath(sprintf('.cache/%s.htaccess', $id));
  }

  protected function getResourceContext(): array {
    $path = $this->getResourcePath();
    $contents = '';
    if (file_exists($path)) {
      $contents = file_get_contents($path);
    }

    return [
      'path' => $path,
      'contents' => $contents,
      'resource' => fopen($path, 'a'),
    ];
  }

  protected function tearDown(): void {
    $path = $this->getResourcePath();
    $this->deleteTestFile($path);
  }

}
