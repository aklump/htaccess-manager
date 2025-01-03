<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

require_once __DIR__ . '/../WWWPrefixPlugin.php';

use AKlump\HtaccessManager\Plugin\WWWPrefixPlugin;
use AKlump\HtaccessManager\Tests\Unit\TestingTraits\TestPluginsTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\WWWPrefixPlugin
 */
class WWWPrefixPluginTest extends TestCase {

  use TestPluginsTrait;

  public function testContextConfigForceSSLTrumpsFileForceSSL() {
    $context = [];
    $context['config'] = [
      'force_ssl' => TRUE,
    ];

    $rc = $this->getResourceContext();
    (new WWWPrefixPlugin())($rc['resource'], [
      'www_prefix' => 'remove',
      'force_ssl' => FALSE,
    ], $context);
    fclose($rc['resource']);

    $this->assertStringContainsString("\n  RewriteRule ^ http%{ENV:protossl}://%1%{REQUEST_URI} [L,R=301]\n", file_get_contents($rc['path']));
  }

  public function testInvokeWithConfigValueRemoveAndForceSSLWritesAsExpected() {
    $rc = $this->getResourceContext();
    (new WWWPrefixPlugin())($rc['resource'], [
      'www_prefix' => 'remove',
      'force_ssl' => TRUE,
    ]);
    fclose($rc['resource']);

    $expected = <<<EOD
    <IfModule mod_rewrite.c>
      RewriteEngine on
      # Remove the leading "www." prefix
      RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]
      RewriteRule ^ https://%1%{REQUEST_URI} [L,R=301]
      # This line is required in some environments, e.g. Lando
      RewriteCond %{ENV:HTTPS} !^.*on
      # This line is more universal but doesn't always work.
      RewriteCond %{HTTPS} !^.*on
      RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    </IfModule>
    EOD;
    $this->assertStringContainsString($expected, file_get_contents($rc['path']));
  }

  public function testInvokeWithConfigValueRemoveWritesAsExpected() {
    $rc = $this->getResourceContext();
    (new WWWPrefixPlugin())($rc['resource'], ['www_prefix' => 'remove']);
    fclose($rc['resource']);

    $expected = <<<EOD
    <IfModule mod_rewrite.c>
      RewriteEngine on
      # Used to set the appropriate http/https protocol in the rewrite.
      RewriteCond %{HTTPS} on
      RewriteRule ^ - [E=protossl:s]
      # Remove the leading "www." prefix
      RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]
      RewriteRule ^ http%{ENV:protossl}://%1%{REQUEST_URI} [L,R=301]
    </IfModule>
    EOD;
    $this->assertStringContainsString($expected, file_get_contents($rc['path']));
  }

  public function testInvokeWithConfigValueAddAndForceSSLWritesAsExpected() {
    $rc = $this->getResourceContext();
    (new WWWPrefixPlugin())($rc['resource'], [
      'www_prefix' => 'add',
      'force_ssl' => TRUE,
    ]);
    fclose($rc['resource']);

    $expected = <<<EOD
    <IfModule mod_rewrite.c>
      RewriteEngine on
      # Ensure the domain has the leading "www." prefix
      RewriteCond %{HTTP_HOST} !^www\. [NC]
      RewriteRule ^ https://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
      # This line is required in some environments, e.g. Lando
      RewriteCond %{ENV:HTTPS} !^.*on
      # This line is more universal but doesn't always work.
      RewriteCond %{HTTPS} !^.*on
      RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    </IfModule>
    EOD;
    $this->assertStringContainsString($expected, file_get_contents($rc['path']));
  }

  public function testInvokeWithConfigValueAddWritesAsExpected() {
    $rc = $this->getResourceContext();
    (new WWWPrefixPlugin())($rc['resource'], ['www_prefix' => 'add']);
    fclose($rc['resource']);

    $expected = <<<EOD
    <IfModule mod_rewrite.c>
      RewriteEngine on
      # Used to set the appropriate http/https protocol in the rewrite.
      RewriteCond %{HTTPS} on
      RewriteRule ^ - [E=protossl:s]
      # Ensure the domain has the leading "www." prefix
      RewriteCond %{HTTP_HOST} !^www\. [NC]
      RewriteRule ^ http%{ENV:protossl}://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    </IfModule>
    EOD;
    $this->assertStringContainsString($expected, file_get_contents($rc['path']));
  }

  public function testAutoDetectAddForceSSLFalseUsesEnvironmentProtocolForRedirect() {
    $rc = $this->getResourceContext();
    (new WWWPrefixPlugin())($rc['resource'], [
      'valid_hosts' => [
        'https://www.website.com/',
      ],
      'force_ssl' => FALSE,
    ]);
    fclose($rc['resource']);
    $content = file_get_contents($rc['path']);
    $this->assertStringContainsString("\n  RewriteCond %{HTTP_HOST} !^www\. [NC]\n", $content, "Assert www. prefix is only added when missing.");
    $this->assertStringContainsString("\n  RewriteRule ^ http%{ENV:protossl}://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]\n", $content, "Protocol is using server to determine http/https protocol.");
  }

  public function testInvokeWithConfigValueDefaultDoesNotChangeFile() {
    $rc = $this->getResourceContext();
    (new WWWPrefixPlugin())($rc['resource'], ['www_prefix' => 'default']);
    fclose($rc['resource']);
    $this->assertSame($rc['contents'], file_get_contents($rc['path']));
  }

  public function testInvokeWithEmptyConfigDoesNotChangeFile() {
    $rc = $this->getResourceContext();
    (new WWWPrefixPlugin())($rc['resource'], []);
    fclose($rc['resource']);
    $this->assertSame($rc['contents'], file_get_contents($rc['path']));
  }

  public function testGetPriority() {
    $this->assertSame(10, WWWPrefixPlugin::getPriority());
  }

}
