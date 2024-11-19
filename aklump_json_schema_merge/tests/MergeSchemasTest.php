<?php

namespace AKlump\JsonSchema\Merge\Tests;

use AKlump\HtaccessManager\Tests\Unit\TestingTraits\TestWithFilesTrait;
use AKlump\JsonSchema\Merge\MergeSchemas;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\aklump_merge_schemas\MergeSchemas
 */
class MergeSchemasTest extends TestCase {

  use TestWithFilesTrait;

  public function testFoo() {
    $schemas = [];
    $schemas[] = $this->getTestFileFilepath('json_schema/alpha.schema.json');
    $schemas[] = $this->getTestFileFilepath('json_schema/bravo.schema.json');
    $schemas = array_map('file_get_contents', $schemas);
    $schemas = array_map(function ($json) {
      return json_decode($json, TRUE);
    }, $schemas);

    $result = (new MergeSchemas())(...$schemas);

    file_put_contents($this->getTestFileFilepath('.cache/merged.schema.json'), json_encode($result, JSON_PRETTY_PRINT));

    $this->assertSame('http://json-schema.org/draft-07/schema#', $result['$schema']);
    $this->assertSame('lorem', $result['$id']);
    $this->assertSame('Lorem Schema', $result['title']);
    $this->assertSame('object', $result['type']);

    $this->assertCount(5, $result['properties']);
    $this->assertArrayHasKey('alpha', $result['properties']);
    $this->assertArrayHasKey('bravo', $result['properties']);

    $this->assertArrayHasKey('charlie', $result['properties']);
    $this->assertSame('Lorem ipsum. Dolar sit amet.', $result['properties']['charlie']['description']);

    $this->assertArrayHasKey('delta', $result['properties']);
    $this->assertSame([
      'array',
      'string',
    ], $result['properties']['delta']['type']);


    $this->assertArrayHasKey('fruits', $result['properties']);

    $this->assertArrayHasKey('apple', $result['properties']['fruits']['patternProperties']['.+']['properties']);
    $this->assertContains('apple', $result['properties']['fruits']['patternProperties']['.+']['required']);

    $this->assertArrayHasKey('banana', $result['properties']['fruits']['patternProperties']['.+']['properties']);
    $this->assertContains('banana', $result['properties']['fruits']['patternProperties']['.+']['required']);
  }
}
