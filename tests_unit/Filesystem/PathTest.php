<?php

namespace AKlump\HtaccessManager\Tests\Unit\Filesystem;

use AKlump\HtaccessManager\Filesystem\Path;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Filesystem\Path
 */
class PathTest extends TestCase {

  public function testIsAbsolute() {
    $this->assertTrue(Path::isAbsolute('/foo/bar'));
    $this->assertFalse(Path::isAbsolute('foo/bar'));
  }

  public function testMakeAbsolute() {
    $base_path = '/base/path';
    $this->assertEquals('/base/path/foo', Path::makeAbsolute('foo', $base_path));
    $this->assertEquals('/already/absolute', Path::makeAbsolute('/already/absolute', $base_path));

    // Test with real file if possible, but realpath depends on existence
    $temp_file = tempnam(sys_get_temp_dir(), 'test');
    $this->assertEquals(realpath($temp_file), Path::makeAbsolute($temp_file, $base_path));
    unlink($temp_file);
  }

  public function testMakeRelative() {
    $base_path = '/base/path/';
    $path = '/base/path/foo/bar';
    // Symfony Filesystem::makePathRelative returns relative path with trailing slash for directories
    $this->assertEquals('foo/bar/', Path::makeRelative($path, $base_path));

    // Test with relative path (should return as is if not absolute)
    $this->assertEquals('already/relative', Path::makeRelative('already/relative', $base_path));

    // Test with a file
    $temp_file = tempnam(sys_get_temp_dir(), 'test_file');
    $base_dir = dirname($temp_file);
    $filename = basename($temp_file);
    $this->assertEquals($filename, Path::makeRelative($temp_file, $base_dir . '/'));
    unlink($temp_file);
  }

  public function testGetExtension() {
    $this->assertEquals('txt', Path::getExtension('file.txt'));
    $this->assertEquals('TXT', Path::getExtension('file.TXT'));
    $this->assertEquals('txt', Path::getExtension('file.TXT', TRUE));
    $this->assertEquals('', Path::getExtension('file'));
    $this->assertEquals('', Path::getExtension(''));
  }
}
