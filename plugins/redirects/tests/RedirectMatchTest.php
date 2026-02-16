<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

use AKlump\HtaccessManager\Plugin\RedirectMatch;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\RedirectMatch
 * @covers \AKlump\HtaccessManager\Plugin\PathProcessorTrait
 */
class RedirectMatchTest extends TestCase {

  public static function dataFortestInvokeProvider(): array {
    $tests = [];
    $tests[] = ['foo', '/foo'];
    $tests[] = ['/foo', '/foo'];
    $tests[] = ['^foo', '^/foo'];
    $tests[] = ['^/foo', '^/foo'];
    $tests[] = ['#^foo#', '#^/foo#'];
    $tests[] = ['#^/foo#', '#^/foo#'];
    $tests[] = ['#foo#', '#/foo#'];
    $tests[] = ['#/foo#', '#/foo#'];

    return $tests;
  }

  /**
   * @dataProvider dataFortestInvokeProvider
   */
  public function testInvoke($path, $expected) {
    $this->assertSame($expected, (new RedirectMatch())($path));
  }
}
