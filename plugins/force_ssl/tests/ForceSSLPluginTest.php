<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

require_once __DIR__ . '/../ForceSSLPlugin.php';

use AKlump\HtaccessManager\Plugin\ForceSSLPlugin;
use AKlump\HtaccessManager\Tests\Unit\TestingTraits\TestPluginsTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\ForceSSLPlugin
 */
class ForceSSLPluginTest extends TestCase {

  use TestPluginsTrait;

  public function testInvokeWithForceSSLTrueWriteExpected() {
    $rc = $this->getResourceContext();
    (new ForceSSLPlugin())($rc['resource'], [
      'force_ssl' => TRUE,
    ]);
    fclose($rc['resource']);
    $expected = <<<EOD
    <IfModule mod_rewrite.c>
      RewriteEngine on
      # This line is required in some environments, e.g. Lando
      RewriteCond %{ENV:HTTPS} !^.*on
      # This line is more universal but doesn't always work.
      RewriteCond %{HTTPS} !^.*on
      RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    </IfModule>
    EOD;
    $this->assertStringContainsString($expected, file_get_contents($rc['path']));
  }

  public function testInvokeWithForceSSLFalseWriteExpected() {
    $rc = $this->getResourceContext();
    (new ForceSSLPlugin())($rc['resource'], [
      'force_ssl' => FALSE,
    ]);
    fclose($rc['resource']);
    $this->assertSame('', file_get_contents($rc['path']));
  }

  public function testInvokeWithWWWPrefixConfigValueRemoveDoesNotChangeFile() {
    $rc = $this->getResourceContext();
    (new ForceSSLPlugin())($rc['resource'], [
      'force_ssl' => TRUE,
      'www_prefix' => 'remove',
    ]);
    fclose($rc['resource']);
    $this->assertSame('', file_get_contents($rc['path']));
  }

  public function testInvokeWithWWWPrefixConfigValueAddDoesNotChangeFile() {
    $rc = $this->getResourceContext();
    (new ForceSSLPlugin())($rc['resource'], [
      'force_ssl' => TRUE,
      'www_prefix' => 'add',
    ]);
    fclose($rc['resource']);
    $this->assertSame('', file_get_contents($rc['path']));
  }

  public function testInvokeWithEmptyConfigDoesNotChangeFile() {
    $rc = $this->getResourceContext();
    (new ForceSSLPlugin())($rc['resource'], []);
    fclose($rc['resource']);
    $this->assertSame($rc['contents'], file_get_contents($rc['path']));
  }

  public function testGetPriority() {
    $this->assertSame(10, ForceSSLPlugin::getPriority());
  }

}
