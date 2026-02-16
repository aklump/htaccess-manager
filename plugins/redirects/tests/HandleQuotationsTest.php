<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

require_once __DIR__ . '/../HandleQuotations.php';

use AKlump\HtaccessManager\Plugin\HandleQuotations;

/**
 * @covers \AKlump\HtaccessManager\Plugin\HandleQuotations
 */
class HandleQuotationsTest extends \PHPUnit\Framework\TestCase {

  public static function dataFortestInvokeProvider(): array {
    $tests = [];

    // @url https://stackoverflow.com/a/14164198
    $tests[] = [
      '/commercial%20work.html',
      '"/commercial work.html"',
    ];
    $tests[] = [
      '/foo',
      '/foo',
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestInvokeProvider
   */
  public function testInvoke($url, $expected) {
    $this->assertSame($expected, (new HandleQuotations())($url));
  }
}
