<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin\Plugin;

use AKlump\HtaccessManager\Plugin\SSLTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\ForceSSLPlugin
 */
class SSLTraitTest extends TestCase {

  public function testGetWWWPrefixValueReturnsDefaultAsExpected() {
    $value = (new TestableSSL())('getWWWPrefixValue', []);
    $this->assertSame('default', $value);

    $value = (new TestableSSL())('getWWWPrefixValue', [
      'valid_hosts' => [
        'https://website.com',
        'https://www.website-backup.com',
      ],
    ]);
    $this->assertSame('default', $value);
  }

  public function testGetWWWPrefixValueReturnsAddWhenExpected() {
    $value = (new TestableSSL())('getWWWPrefixValue', ['www_prefix' => 'add']);
    $this->assertSame('add', $value);

    $value = (new TestableSSL())('getWWWPrefixValue', [
      'valid_hosts' => [
        'https://www.website.com',
        'https://www.website-backup.com',
      ],
    ]);
    $this->assertSame('add', $value);
  }

  public function testGetWWWPrefixValueReturnsRemoveWhenExpected() {
    $value = (new TestableSSL())('getWWWPrefixValue', ['www_prefix' => 'remove']);
    $this->assertSame('remove', $value);

    $value = (new TestableSSL())('getWWWPrefixValue', [
      'valid_hosts' => [
        'https://website.com',
        'https://website-backup.com',
      ],
    ]);
    $this->assertSame('remove', $value);
  }

  public function testGetForceSSLConfigValue() {
    $value = (new TestableSSL())('getForceSSLConfigValue', ['force_ssl' => TRUE]);
    $this->assertTrue($value);

    $value = (new TestableSSL())('getForceSSLConfigValue', ['force_ssl' => FALSE]);
    $this->assertFalse($value);

    $value = (new TestableSSL())('getForceSSLConfigValue', [
      'valid_hosts' => [
        'http://website.com',
        'http://website-backup.com',
      ],
    ]);
    $this->assertFalse($value);

    $value = (new TestableSSL())('getForceSSLConfigValue', [
      'valid_hosts' => [
        'https://website.com',
        'https://website-backup.com',
      ],
    ]);
    $this->assertTrue($value);

    $value = (new TestableSSL())('getForceSSLConfigValue', [
      'valid_hosts' => [
        'https://website.com',
        'http://website-backup.com',
      ],
    ]);
    $this->assertFalse($value);
  }
}

class TestableSSL {

  use SSLTrait;

  public function __invoke(string $method, ...$args) {
    return $this->{$method}(...$args);
  }
}
