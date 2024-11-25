<?php

namespace AKlump\HtaccessManager\Tests\Unit\Config;

use AKlump\HtaccessManager\Config\LoadConfig;
use AKlump\HtaccessManager\Tests\Unit\TestingTraits\TestWithFilesTrait;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Config\LoadConfig
 * @uses   \AKlump\HtaccessManager\Config\NormalizeConfig
 * @uses   \AKlump\HtaccessManager\Plugin\MergePluginSchemas
 * @uses   \AKlump\PluginFramework\GetPlugins
 * @uses   \AKlump\JsonSchema\MergeSchemas
 */
class LoadConfigTest extends TestCase {

  use TestWithFilesTrait;

  public function testRelativePathThrows() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('must be absolute');
    (new LoadConfig([]))('lorem/ipsum');
  }

  public function testMissingFileThrows() {
    $this->deleteTestFile('.cache');
    $config_path = $this->getTestFileFilepath('.cache/config.yml');
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Missing');
    (new LoadConfig([]))($config_path);
  }


  public function testOutputPathsAreMadeAbsoluteAndStringsAreMadeArrays() {
    $config_path = $this->getTestFileFilepath('alpha/config.yml');
    $config = (new LoadConfig([]))($config_path);
    $expected = [$this->getTestFileFilepath('alpha/web/.htaccess.staging')];
    $this->assertSame($expected, $config['files']['staging_webroot']['output']);
  }

  public function testSourcePathsAreMadeAbsolute() {
    $config_path = $this->getTestFileFilepath('alpha/config.yml');
    $config = (new LoadConfig([]))($config_path);

    $expected = $this->getTestFileFilepath('alpha/apache/.htaccess.banned_ips');
    $this->assertContains($expected, $config['files']['prod_webroot']['source']);

    $expected = $this->getTestFileFilepath('alpha/apache/webroot/.htaccess.custom');
    $this->assertContains($expected, $config['files']['prod_webroot']['source']);
    $this->assertContains('https://raw.githubusercontent.com/drupal/drupal/7.x/.htaccess', $config['files']['prod_webroot']['source']);
  }

}
