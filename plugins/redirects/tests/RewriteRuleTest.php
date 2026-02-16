<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

use AKlump\HtaccessManager\Plugin\RewriteRule;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\RewriteRule
 * @covers \AKlump\HtaccessManager\Plugin\PathProcessorTrait
 */
class RewriteRuleTest extends TestCase {

  public static function dataFortestInvokeProvider(): array {
    $tests = [];
    $tests[] = ['/foo', 'foo'];
    $tests[] = ['^/foo', '^foo'];
    $tests[] = ['#^/foo#', '#^foo#'];
    $tests[] = ['#/foo#', '#foo#'];
    $tests[] = ['foo', 'foo'];
    $tests[] = ['^foo', '^foo'];
    $tests[] = ['#^foo#', '#^foo#'];

    return $tests;
  }

  /**
   * @dataProvider dataFortestInvokeProvider
   */
  public function testInvoke($path, $expected) {
    $this->assertSame($expected, (new RewriteRule())($path));
  }
}
