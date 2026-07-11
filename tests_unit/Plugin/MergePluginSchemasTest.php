<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

use AKlump\HtaccessManager\Plugin\MergePluginSchemas;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\MergePluginSchemas
 * @uses \AKlump\JsonSchema\MergeSchemas
 */
class MergePluginSchemasTest extends TestCase {

  public function testInvoke() {
    $temp_schema_file = tempnam(sys_get_temp_dir(), 'schema');
    file_put_contents($temp_schema_file, json_encode([
      'properties' => [
        'plugin_setting' => ['type' => 'string'],
      ],
    ]));

    $plugins = [
      'foo' => [
        'config_schema' => $temp_schema_file,
      ],
      'bar' => [
        // No config_schema
      ],
    ];

    $merge = new MergePluginSchemas($plugins);

    $base_schema = [
      'properties' => [
        'base_setting' => ['type' => 'integer'],
      ],
    ];

    $result = $merge('some_id', $base_schema);

    $this->assertArrayHasKey('base_setting', $result['properties']);
    $this->assertArrayHasKey('plugin_setting', $result['properties']);
    $this->assertEquals('integer', $result['properties']['base_setting']['type']);
    $this->assertEquals('string', $result['properties']['plugin_setting']['type']);

    unlink($temp_schema_file);
  }
}
