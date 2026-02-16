<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

use AKlump\HtaccessManager\Plugin\WriteErrorHandler;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\WriteErrorHandler
 */
class WriteErrorHandlerTest extends TestCase {

  public function testInvoke() {
    $tempFile = tempnam(sys_get_temp_dir(), 'error_handler');
    $handler = new WriteErrorHandler();
    $handler(404, $tempFile);

    $content = file_get_contents($tempFile);
    $this->assertStringContainsString('http_response_code(404)', $content);
    $this->assertStringContainsString("echo '404 Not Found'", $content);

    unlink($tempFile);
  }

  public function testInvokeThrowsOnInvalidPath() {
    $this->expectException(\RuntimeException::class);
    $handler = new WriteErrorHandler();
    $handler(404, '/non/existent/path/for/error/handler.php');
  }
}
