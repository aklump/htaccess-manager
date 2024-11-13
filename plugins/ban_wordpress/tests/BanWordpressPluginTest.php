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

    $expected = <<<EOD
    <IfModule mod_rewrite.c>
      RewriteEngine on
      RewriteRule ^wp-login.php$ - [R=410,L]
    </IfModule>
    EOD;

    $this->assertStringContainsString($expected, file_get_contents($rc['path']));
  }

  public function testInvokeWithEmptyConfigDoesNotChangeFile() {
    $rc = $this->getResourceContext();
    (new BanWordpressPlugin())($rc['resource'], []);
    fclose($rc['resource']);
    $this->assertSame($rc['contents'], file_get_contents($rc['path']));
  }

  public function testGetPriority() {
    $this->assertSame(0, BanWordpressPlugin::getPriority());
  }

}
