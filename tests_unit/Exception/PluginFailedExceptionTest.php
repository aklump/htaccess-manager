<?php

namespace AKlump\HtaccessManager\Tests\Unit\Exception;

use AKlump\HtaccessManager\Exception\PluginFailedException;
use AKlump\HtaccessManager\Plugin\PluginInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Exception\PluginFailedException
 */
class PluginFailedExceptionTest extends TestCase {

  public function testExceptionMessage() {
    $plugin = $this->createMock(PluginInterface::class);
    $plugin->method('getName')->willReturn('Foo');

    $exception = new PluginFailedException($plugin, 'Something went wrong');
    $this->assertEquals('Foo plugin failed: Something went wrong', $exception->getMessage());
  }
}
