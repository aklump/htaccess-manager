<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

require_once __DIR__ . '/../RedirectsPlugin.php';

use AKlump\HtaccessManager\Exception\ConfigurationException;
use AKlump\HtaccessManager\redirects\RedirectsPlugin;
use AKlump\HtaccessManager\Tests\Unit\TestingTraits\TestPluginsTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\redirects\RedirectsPlugin
 */
class RedirectsPluginTest extends TestCase {

  use TestPluginsTrait;

  public function testInvokeInheritTrueIncludesGlobal() {
    $rc = $this->getResourceContext();
    $context = [];
    $context['config'] = [
      'redirects' => [
        301 => [
          '/some/old/path /some/new/path',
          '/foo/(.+) /bar/$1',
        ],
        403 => [
          '/some/forbidden/path',
        ],
      ],
    ];
    (new RedirectsPlugin())($rc['resource'], [
      'redirects' => [
        'inherit' => TRUE,
        301 => [
          '/another/old/path /another/new/path',
        ],
      ],
    ], $context);
    fclose($rc['resource']);
    $content = file_get_contents($rc['path']);

    $expected = <<<EOD
    RedirectMatch 301 ^/another/old/path/?\$ /another/new/path
    RedirectMatch 301 ^/some/old/path/?\$ /some/new/path
    RedirectMatch 301 ^/foo/(.+)/?\$ /bar/$1
    RedirectMatch 403 ^/some/forbidden/path/?\$
    EOD;
    $this->assertStringContainsString($expected, $content);
  }

  public function testInvokeRulesRemoveDuplicates() {
    $rc = $this->getResourceContext();
    $context = [];
    $context['config'] = [
      'redirects' => [
        301 => [
          '/some/old/path /some/new/path',
        ],
      ],
    ];
    (new RedirectsPlugin())($rc['resource'], [
      'redirects' => [
        'inherit' => TRUE,
        301 => [
          '/some/old/path /some/new/path',
          '/some/old/path /some/new/path',
        ],
      ],
    ], $context);
    fclose($rc['resource']);
    $content = file_get_contents($rc['path']);

    $expected = <<<EOD
    #
    #
    # Redirects Plugin
    #
    RedirectMatch 301 ^/some/old/path/?$ /some/new/path
    # End Redirects plugin
    
    
    EOD;
    $this->assertSame($expected, $content);
  }

  public function testInvokeInheritFalseIgnoresGlobal() {
    $rc = $this->getResourceContext();
    $context = [];
    $context['config'] = [
      'redirects' => [
        301 => [
          '/some/old/path /some/new/path',
        ],
      ],
    ];
    (new RedirectsPlugin())($rc['resource'], [
      'redirects' => [
        'inherit' => FALSE,
        301 => [
          '/another/old/path /another/new/path',
        ],
      ],
    ], $context);
    fclose($rc['resource']);
    $content = file_get_contents($rc['path']);

    $this->assertStringContainsString("\nRedirectMatch 301 ^/another/old/path/?\$ /another/new/path\n", $content);
    $this->assertStringNotContainsString("/some/old/path", $content);
  }

  public function testInvokeInheritGlobalDefaultsTrue() {
    $rc = $this->getResourceContext();
    $context = [];
    $context['config'] = [
      'redirects' => [
        301 => [
          '/some/old/path /some/new/path',
        ],
      ],
    ];
    (new RedirectsPlugin())($rc['resource'], [], $context);
    fclose($rc['resource']);
    $content = file_get_contents($rc['path']);

    $this->assertStringContainsString("\nRedirectMatch 301 ^/some/old/path/?\$ /some/new/path\n", $content);
  }

  public static function dataFortestInvokeQuotesSpecialCharsInUrlsProvider(): array {
    $tests = [];
    $tests[] = [
      '/sites/default/files/downloads/Gamo%20Fact%20Sheet.pdf /sites/default/files/lesson-plans/gamo_fact_sheet.pdf',
      '"^/sites/default/files/downloads/Gamo Fact Sheet.pdf/?$" /sites/default/files/lesson-plans/gamo_fact_sheet.pdf'
    ];
    $tests[] = [
      '/foo%20bar/baz /lorem.php',
      '"^/foo bar/baz/?$" /lorem.php',
    ];
    $tests[] = [
      '(.+) /index.php?q=$1',
      '^(.+)/?$ /index.php?q=$1',
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestInvokeQuotesSpecialCharsInUrlsProvider
   */
  public function testInvokeQuotesSpecialCharsInUrls($url, $quoted_url) {
    $rc = $this->getResourceContext();
    (new RedirectsPlugin())($rc['resource'], [
      'redirects' => [
        'inherit' => FALSE,
        301 => [$url],
      ],
    ]);
    fclose($rc['resource']);
    $content = file_get_contents($rc['path']);

    $this->assertStringContainsString($quoted_url, $content);
  }

  public function testInvokeWithRedirectsFalseDoesNotChangeFile() {
    $rc = $this->getResourceContext();
    (new RedirectsPlugin())($rc['resource'], [
      'redirects' => [],
    ]);
    fclose($rc['resource']);
    $this->assertSame($rc['contents'], file_get_contents($rc['path']));
  }

  /**
   * In the original website_htaccess there was a bug that required the redirect
   * target to escape the $1, this tests that we catch that for the upgrade
   * path.
   *
   * @return void
   */
  public function testEscapedCaptureGroupThrows() {
    $rc = $this->getResourceContext();
    $this->expectException(ConfigurationException::class);
    $this->expectExceptionMessage('301 redirect');
    $this->expectExceptionMessage('/sites/default/\$1');
    $this->expectExceptionMessage('/sites/default/$1');
    (new RedirectsPlugin())($rc['resource'], [
      'redirects' => [
        301 => ['/sites/website.com/(.+) /sites/default/\$1'],
      ],
    ]);
    fclose($rc['resource']);
  }

  public function testInvokeWithEmptyConfigDoesNotChangeFile() {
    $rc = $this->getResourceContext();
    (new RedirectsPlugin())($rc['resource'], []);
    fclose($rc['resource']);
    $this->assertSame($rc['contents'], file_get_contents($rc['path']));
  }

  public function testGetPriority() {
    $this->assertSame(10, RedirectsPlugin::getPriority());
  }

}
