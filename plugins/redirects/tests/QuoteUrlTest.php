<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

require_once __DIR__ . '/../QuoteUrl.php';

use AKlump\HtaccessManager\redirects\QuoteUrl;

/**
 * @covers \AKlump\HtaccessManager\redirects\QuoteUrl
 */
class QuoteUrlTest extends \PHPUnit\Framework\TestCase {

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
    $this->assertSame($expected, (new QuoteUrl())($url));
  }
}
