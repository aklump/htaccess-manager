<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

require_once __DIR__ . '/../SourcePlugin.php';

use AKlump\HtaccessManager\Config\LoadConfig;
use AKlump\HtaccessManager\Exception\PluginFailedException;
use AKlump\HtaccessManager\Plugin\SourcePlugin;
use AKlump\HtaccessManager\Tests\Unit\TestingTraits\TestWithFilesTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\SourcePlugin
 * @uses   \AKlump\HtaccessManager\Config\LoadConfig
 * @uses   \AKlump\HtaccessManager\Exception\PluginFailedException
 * @uses   \AKlump\HtaccessManager\Config\NormalizeConfig
 * @uses   \AKlump\HtaccessManager\Plugin\GetPlugins
 * @uses   \AKlump\HtaccessManager\Plugin\MergePluginSchemas
 * @uses   \AKlump\HtaccessManager\JsonSchemaMerge\MergeSchemas
 * @uses   \AKlump\HtaccessManager\Helper\GetShortPath
 *
 */
class SourcePluginTest extends TestCase {

  use TestWithFilesTrait;

  public function testStrangeValueThrows() {
    $this->deleteTestFile('.cache');
    $output_path = $this->getTestFileFilepath('.cache/alpha.htaccess');
    $config_path = $this->getTestFileFilepath('.cache/config.yml');
    $this->expectException(PluginFailedException::class);
    $output_resource = fopen($output_path, 'a');
    (new SourcePlugin())($output_resource, ['source' => [$config_path]]);
    fclose($output_resource);
    $this->assertFileDoesNotExist($output_path);
  }

  public function testEmptySourceDoesWriteToFile() {
    $this->deleteTestFile('.cache');
    $output_path = $this->getTestFileFilepath('.cache/alpha.htaccess');
    $output_resource = fopen($output_path, 'a');
    $position_before = ftell($output_resource);
    (new SourcePlugin())($output_resource, []);
    $this->assertSame($position_before, ftell($output_resource));
    fclose($output_resource);
  }

  public function testInvoke() {
    $this->deleteTestFile('.cache');
    $output_path = $this->getTestFileFilepath('.cache/alpha.htaccess');
    $config_path = $this->getTestFileFilepath('alpha/config.yml');
    $config = (new LoadConfig([]))($config_path);
    $config = $config['files']['prod_webroot'];

    $this->assertFileDoesNotExist($output_path);
    $output_resource = fopen($output_path, 'a');
    (new SourcePlugin())($output_resource, $config);
    fclose($output_resource);
    $this->assertFileExists($output_path);

    $content = file_get_contents($output_path);

    $this->assertStringContainsString('# Copied from /Users/aklump/Code/Packages/php/htaccess_manager/tests_unit/test_files/alpha/apache/.htaccess.banned_ips', $content, 'Assert correct header');
    $this->assertStringContainsString('# .htaccess.banned_ips', $content);

    $this->assertStringContainsString('# Copied from /Users/aklump/Code/Packages/php/htaccess_manager/tests_unit/test_files/alpha/apache/webroot/.htaccess.custom', $content, 'Assert correct header');
    $this->assertStringContainsString('# .htaccess.custom', $content);

    $this->assertStringContainsString('# Downloaded from https://raw.githubusercontent.com/drupal/drupal/7.x/.htaccess', $content, 'Assert correct header');
    $this->assertStringContainsString('# Apache/PHP/Drupal settings:', $content);
  }

  public function testGetPriority() {
    $this->assertSame(0, SourcePlugin::getPriority());
  }
}
