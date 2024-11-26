<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin\Plugin;

use AKlump\HtaccessManager\Config\Defaults;
use AKlump\HtaccessManager\Plugin\FileHeader;
use AKlump\HtaccessManager\Tests\Unit\TestingTraits\TestPluginsTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\FileHeader
 */
class FileHeaderTest extends TestCase {

  use TestPluginsTrait;

  public function testEmptyConfigHeader() {
    $context = [];
    $context['config'] = [
      'header' => '',
    ];
    $rc = $this->getResourceContext();
    (new FileHeader())($rc['resource'], [
    ], $context);
    fclose($rc['resource']);

    $content = file_get_contents($rc['path']);
    $this->assertStringStartsWith(sprintf("#\n#\n# %s\n#\n# (Built on: ", Defaults::OUTPUT_FILE_TITLE), $content);
  }
  public function testNoConfigHeader() {
    $context = [];
    $context['config'] = [
    ];
    $rc = $this->getResourceContext();
    (new FileHeader())($rc['resource'], [
    ], $context);
    fclose($rc['resource']);

    $content = file_get_contents($rc['path']);
    $this->assertStringStartsWith(sprintf("#\n#\n# %s\n#\n# %s\n# (Built on: ", Defaults::OUTPUT_FILE_TITLE, Defaults::OUTPUT_FILE_HEADER), $content);
  }
}
