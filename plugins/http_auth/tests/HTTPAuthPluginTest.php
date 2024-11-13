<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

require_once __DIR__ . '/../HTTPAuthPlugin.php';

use AKlump\HtaccessManager\Plugin\HTTPAuthPlugin;
use AKlump\HtaccessManager\Tests\Unit\TestingTraits\TestPluginsTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\HTTPAuthPlugin
 */
class HTTPAuthPluginAuthPluginTest extends TestCase {

  use TestPluginsTrait;

  public function testInvokeWithNoWhitelistWritesAsExpected() {
    $rc = $this->getResourceContext();
    (new HTTPAuthPlugin())($rc['resource'], [
      'http_auth' => [
        'title' => 'Lorem title',
        'user_file' => '/.shared_passwords',
        'whitelist' => [],
      ],
    ]);
    fclose($rc['resource']);
    $content = file_get_contents($rc['path']);

    $this->assertStringContainsString("\nAuthName \"Lorem title\"\n", $content);

    $this->assertStringContainsString("\nAuthUserFile \"/.shared_passwords\"\n", $content);

    $expected = <<<EOD
    <IfModule mod_authz_groupfile.c>
      AuthGroupFile /dev/null
    </IfModule>
    AuthType Basic
    Require valid-user
    
    EOD;
    $this->assertStringContainsString($expected, $content);
  }

  public function testInvokeWithWhitelistWritesAsExpected() {
    $rc = $this->getResourceContext();
    (new HTTPAuthPlugin())($rc['resource'], [
      'http_auth' => [
        'title' => 'Lorem title',
        'user_file' => '/.shared_passwords',
        'whitelist' => [
          '1.1.1.1',
          '3.3.3.3',
        ],
      ],
    ]);
    fclose($rc['resource']);
    $content = file_get_contents($rc['path']);

    $this->assertStringContainsString("\nAuthName \"Lorem title\"\n", $content);

    $this->assertStringContainsString("\nAuthUserFile \"/.shared_passwords\"\n", $content);

    $expected = <<<EOD
    <IfModule mod_authz_groupfile.c>
      AuthGroupFile /dev/null
    </IfModule>
    AuthType Basic
    Require valid-user
    
    EOD;
    $this->assertStringContainsString($expected, $content);

    $expected = "\nAllow from 1.1.1.1\nAllow from 3.3.3.3\nSatisfy any\n";
    $this->assertStringContainsString($expected, $content);
  }

  public function testInvokeWithEmptyConfigDoesNotChangeFile() {
    $rc = $this->getResourceContext();
    (new HTTPAuthPlugin())($rc['resource'], []);
    fclose($rc['resource']);
    $this->assertSame($rc['contents'], file_get_contents($rc['path']));
  }

  public function testGetPriority() {
    $this->assertSame(0, HTTPAuthPlugin::getPriority());
  }

}
