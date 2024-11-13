<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

require_once __DIR__ . '/../HotlinksPlugin.php';

use AKlump\HtaccessManager\Plugin\HotlinksPlugin;
use AKlump\HtaccessManager\Tests\Unit\TestingTraits\TestPluginsTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\HotlinksPlugin
 */
class HotlinksPluginTest extends TestCase {

  use TestPluginsTrait;

  public function testTrailingSlashIsStrippedFromHosts() {
    $rc = $this->getResourceContext();
    (new HotlinksPlugin())($rc['resource'], [
      'hotlinks' => [
        'deny' => ['gif', 'jpg'],
      ],
      'valid_hosts' => [
        'https://www.my-project.edu/',
      ],
    ]);
    fclose($rc['resource']);

    $this->assertStringContainsString("\n  RewriteCond %{HTTP_HOST} !^www.my-project.edu$ [NC]\n", file_get_contents($rc['path']));
  }

  public function testInvoke() {
    $rc = $this->getResourceContext();
    (new HotlinksPlugin())($rc['resource'], [
      'hotlinks' => [
        'deny' => ['gif', 'jpg'],
      ],
      'valid_hosts' => [
        'https://www.my-project.edu',
      ],
    ]);
    fclose($rc['resource']);

    $expected = <<<EOD
    <IfModule mod_rewrite.c>
      RewriteEngine on
      RewriteCond %{HTTP_REFERER} !^$
      RewriteCond %{HTTP_HOST} !^www.my-project.edu$ [NC]
      RewriteCond %{HTTP_REFERER} !^https://www.my-project.edu(?:$|/) [NC]
      RewriteRule .(gif|jpg)$ - [F,NC]
    </IfModule>
    EOD;
    $this->assertStringContainsString($expected, file_get_contents($rc['path']));
  }

  public function testInvokeWithEmptyConfigDoesNotChangeFile() {
    $rc = $this->getResourceContext();
    (new HotlinksPlugin())($rc['resource'], []);
    fclose($rc['resource']);
    $this->assertSame($rc['contents'], file_get_contents($rc['path']));
  }

  public function testGetPriority() {
    $this->assertSame(20, HotlinksPlugin::getPriority());
  }

}
