<?php

namespace AKlump\HtaccessManager\Tests\Unit\Config;

use AKlump\HtaccessManager\Config\LoadConfig;
use AKlump\HtaccessManager\Tests\Unit\TestingTraits\TestWithFilesTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Config\LoadConfig
 * @uses   \AKlump\HtaccessManager\Config\NormalizeConfig
 * @uses   \AKlump\HtaccessManager\Plugin\MergePluginSchemas
 * @uses   \AKlump\PluginFramework\GetPlugins
 * @uses   \AKlump\JsonSchema\MergeSchemas
 * @uses   \AKlump\HtaccessManager\Helper\SubstituteEnvVars
 */
class LoadConfigEnvVarTest extends TestCase {

  use TestWithFilesTrait;

  public function testEnvVarSubstitutionInConfig() {
    $config_dir = $this->getTestFileFilepath('env_test');
    if (!is_dir($config_dir)) {
      mkdir($config_dir, 0777, true);
    }
    $config_path = $config_dir . '/config.yml';
    $yaml = <<<YAML
files:
  \$PROJ_ID:
    title: \${PROJ_TITLE}
    valid_hosts:
      - https://example.com
    output: \${PROJ_DIR}/web/.htaccess
YAML;
    file_put_contents($config_path, $yaml);

    putenv('PROJ_ID=myproject');
    putenv('PROJ_TITLE=Aklump\'s Web Factory');
    putenv('PROJ_DIR=myproj_dir');

    $config = (new LoadConfig([]))($config_path);

    $this->assertArrayHasKey('myproject', $config['files']);
    $this->assertEquals('Aklump\'s Web Factory', $config['files']['myproject']['title']);
    $expected_output = $config_dir . '/myproj_dir/web/.htaccess';
    $this->assertContains($expected_output, $config['files']['myproject']['output']);

    // Test mixing both syntaxes
    $yaml_mixed = <<<YAML
files:
  myproj:
    title: \$PROJ_TITLE (\${PROJ_ID})
    output: .htaccess
    valid_hosts:
      - https://example.com
YAML;
    file_put_contents($config_path, $yaml_mixed);
    $config = (new LoadConfig([]))($config_path);
    $this->assertEquals('Aklump\'s Web Factory (myproject)', $config['files']['myproj']['title']);

    // Cleanup
    putenv('PROJ_ID');
    putenv('PROJ_TITLE');
    putenv('PROJ_DIR');
    unlink($config_path);
    rmdir($config_dir);
  }
}
