<?php

namespace AKlump\HtaccessManager\Tests\Unit\Helper;

use AKlump\HtaccessManager\Helper\GetShortPath;
use PHPUnit\Framework\TestCase;

/**
  * @covers \AKlump\HtaccessManager\Helper\GetShortPath
  */
class GetShortPathTest extends TestCase {

  public function testInvokeWithSubpath() {
    $basepath = '/var/www/html';
    $path = '/var/www/html/css/style.css';
    $get_short_path = new GetShortPath($basepath);
    $this->assertEquals('css/style.css', $get_short_path($path));
  }

  public function testInvokeWithExternalPath() {
    $basepath = '/var/www/html';
    $path = '/tmp/other.txt';
    $get_short_path = new GetShortPath($basepath);
    $this->assertEquals('/tmp/other.txt', $get_short_path($path));
  }

  public function testInvokeWithCurrentWorkingDirectory() {
    $cwd = getcwd();
    $path = $cwd . '/config.yml';
    $get_short_path = new GetShortPath($cwd);
    $this->assertEquals('./config.yml', $get_short_path($path));
  }
}
