<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin\Helper;

use AKlump\HtaccessManager\Helper\RemoveComments;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Helper\RemoveComments
 */
class RemoveCommentsTest extends TestCase {

  public static function dataForTestInvokeProvider(): array {
    $tests = [];
    $tests[] = [
      '',
      '',
    ];
    $tests[] = [
      'Deny from all',
      'Deny from all',
    ];
    $tests[] = [
      "# Block all traffic\nDeny from all\n",
      "Deny from all\n",
    ];

    return $tests;
  }

  /**
   * @dataProvider dataForTestInvokeProvider
   */
  public function testInvoke(string $source, string $expected) {
    $this->assertSame($expected, (new RemoveComments())($source));
  }
}
