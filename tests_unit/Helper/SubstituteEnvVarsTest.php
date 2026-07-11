<?php

namespace AKlump\HtaccessManager\Tests\Unit\Helper;

use AKlump\HtaccessManager\Helper\SubstituteEnvVars;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Helper\SubstituteEnvVars
 */
class SubstituteEnvVarsTest extends TestCase {

  public function testInvokeWithVariables() {
    putenv('FOO=bar');
    putenv('BAZ=qux');

    $substitute = new SubstituteEnvVars();

    // Test simple string
    $this->assertEquals('bar', $substitute('$FOO'));
    $this->assertEquals('bar', $substitute('${FOO}'));
    $this->assertEquals('bar-qux', $substitute('$FOO-$BAZ'));

    // Test non-existent variable
    $this->assertEquals('$NON_EXISTENT', $substitute('$NON_EXISTENT'));

    // Test array values
    $config = [
      'key1' => '$FOO',
      'key2' => ['nested' => '${BAZ}'],
    ];
    $expected = [
      'key1' => 'bar',
      'key2' => ['nested' => 'qux'],
    ];
    $this->assertEquals($expected, $substitute($config));

    // Test array keys
    $config = ['$FOO' => 'value'];
    $expected = ['bar' => 'value'];
    $this->assertEquals($expected, $substitute($config));

    // Test mixed
    $this->assertEquals('plain string', $substitute('plain string'));
    $this->assertEquals(123, $substitute(123));

    putenv('FOO');
    putenv('BAZ');
  }
}
