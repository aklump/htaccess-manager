<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

require_once __DIR__ . '/../BanIPsPlugin.php';

use AKlump\HtaccessManager\Plugin\BanIPs;
use AKlump\HtaccessManager\Tests\Unit\TestingTraits\TestPluginsTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\BanIPs
 */
class BanIPsPluginTest extends TestCase {

  use TestPluginsTrait;

  public function testInvokeWithInheritFalseDoesNotMerge() {
    $context = [];
    $context['config'] = [
      'ban_ips_inherit' => FALSE,
      'ban_ips' => ['1.1.1.1'],
    ];
    $rc = $this->getResourceContext();
    (new BanIPs())($rc['resource'], [
      'ban_ips' => ['2.2.2.2'],
    ], $context);
    fclose($rc['resource']);

    $content = file_get_contents($rc['path']);
    $this->assertStringNotContainsString("\ndeny from 1.1.1.1\n", $content);
    $this->assertStringContainsString("\ndeny from 2.2.2.2\n", $content);
  }

  public function testInvokeWithInheritTrueMerges() {
    $context = [];
    $context['config'] = [
      'ban_ips_inherit' => TRUE,
      'ban_ips' => ['1.1.1.1'],
    ];
    $rc = $this->getResourceContext();

    (new BanIPs())($rc['resource'], [
      'ban_ips' => ['2.2.2.2'],
    ], $context);
    fclose($rc['resource']);

    $content = file_get_contents($rc['path']);
    $this->assertStringContainsString("\ndeny from 1.1.1.1\n", $content);
    $this->assertStringContainsString("\ndeny from 2.2.2.2\n", $content);
  }

  public function testInvokeWithEmptyConfigDoesNotChangeFile() {
    $rc = $this->getResourceContext();
    (new BanIPs())($rc['resource'], []);
    fclose($rc['resource']);
    $this->assertSame($rc['contents'], file_get_contents($rc['path']));
  }

  public function testGetPriority() {
    $this->assertSame(0, BanIPs::getPriority());
  }

}
