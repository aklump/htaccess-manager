<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin\Config;

use AKlump\HtaccessManager\Config\Defaults;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Config\Defaults
 */
class DefaultsTest extends TestCase {

  public function testOutputFileId() {
    $this->assertNotEmpty(Defaults::OUTPUT_FILE_ID);
  }
}
