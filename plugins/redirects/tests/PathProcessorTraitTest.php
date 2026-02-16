<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

use AKlump\HtaccessManager\Plugin\PathProcessorTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\PathProcessorTrait
 */
class PathProcessorTraitTest extends TestCase {

  public function testGetDelimiter() {
    $dummy = new class {

      use PathProcessorTrait;

      public function callGetDelimiter(string $value): string {
        return $this->getDelimiter($value);
      }
    };

    $this->assertSame('#', $dummy->callGetDelimiter('#foo#'));
    $this->assertSame('/', $dummy->callGetDelimiter('/foo/'));
    $this->assertSame('', $dummy->callGetDelimiter('/foo'));
    $this->assertSame('', $dummy->callGetDelimiter('foo'));
  }
}
