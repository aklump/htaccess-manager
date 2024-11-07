<?php

namespace AKlump\HtaccessManager\Tests\Unit\Config;

use AKlump\HtaccessManager\Config\NormalizeConfig;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Config\NormalizeConfig
 */
class NormalizeConfigTest extends TestCase {

  public function testOutputArrayIsMadeZeroBased() {
    $config['files']['foo']['output'] = [1 => '/foo/bar'];
    $config = (new NormalizeConfig())($config);
    $this->assertSame('/foo/bar', $config['files']['foo']['output'][0]);
  }

  public function testOutputStringToArray() {
    $config = ['files' => ['prod' => ['output' => 'lorem/ipsum']]];
    $config = (new NormalizeConfig())($config);
    $this->assertIsArray($config['files']['prod']['output']);
  }
}
