<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

require_once __DIR__ . '/../BanWordpressPlugin.php';

use AKlump\HtaccessManager\Plugin\BanWordpressPlugin;
use AKlump\HtaccessManager\Tests\Unit\TestingTraits\TestPluginsTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\BanWordpressPlugin
 */
class BanWordpressPluginTest extends TestCase {

  use TestPluginsTrait;

  public function testInvokeWithConfigTrueWritesExpected() {
    $rc = $this->getResourceContext();
    (new BanWordpressPlugin())($rc['resource'], [
      'ban_wordpress' => TRUE,
    ]);
    fclose($rc['resource']);

    $contents = file_get_contents($rc['path']);
    $this->assertStringContainsString('<IfModule mod_alias.c>', $contents);
    $this->assertStringContainsString('RedirectMatch 404 ^/wordpress', $contents);
    $this->assertStringContainsString('RedirectMatch 404 ^/wp-(admin|includes|content)/.*$', $contents);
    $this->assertStringContainsString('RedirectMatch 404 ^/wp-(config|login)\.php$', $contents);
    $this->assertStringContainsString('</IfModule>', $contents);
  }

  public function testInvokeWithEmptyConfigDoesNotChangeFile() {
    $rc = $this->getResourceContext();
    (new BanWordpressPlugin())($rc['resource'], []);
    fclose($rc['resource']);
    $this->assertSame($rc['contents'], file_get_contents($rc['path']));
  }

  public function testGetPriority() {
    $this->assertGreaterThan(0, BanWordpressPlugin::getPriority());
  }

}
