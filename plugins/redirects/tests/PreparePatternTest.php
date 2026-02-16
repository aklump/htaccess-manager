<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin;

use AKlump\HtaccessManager\Plugin\PreparePattern;
use AKlump\HtaccessManager\Plugin\PathProcessorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Plugin\PreparePattern
 */
class PreparePatternTest extends TestCase {

  public static function dataProviderInvokeCases(): array {
    return [
      'auto regex is run before processor; hashes are trimmed' => [
        'input_pattern' => '/foo',
        'processor_return' => '#^/foo/?$#',
        'expected' => '^/foo/?$',
      ],
      'at-delimiters are trimmed' => [
        'input_pattern' => '/foo',
        'processor_return' => '@^/foo/?$@',
        'expected' => '^/foo/?$',
      ],
      'no delimiter means no trimming' => [
        'input_pattern' => '/foo',
        'processor_return' => '^/foo/?$',
        'expected' => '^/foo/?$',
      ],
      'quotations: %20 becomes space and string is wrapped in quotes' => [
        'input_pattern' => '/foo%20bar',
        'processor_return' => '#^/foo%20bar/?$#',
        'expected' => '"^/foo bar/?$"',
      ],
    ];
  }

  /**
   * @dataProvider dataProviderInvokeCases
   */
  public function testInvokeCoversVariations(string $input_pattern, string $processor_return, string $expected): void {
    $processor = $this->createMock(PathProcessorInterface::class);
    $processor->expects($this->once())
      ->method('__invoke')
      ->with($this->callback(function (string $value) use ($input_pattern): bool {
        // Assert AutoRegex ran before the processor:
        // it should turn a raw "/foo" into a delimited pattern.
        if ($input_pattern === '/foo') {
          return $value === '#^/foo/?$#';
        }
        if ($input_pattern === '/foo%20bar') {
          return $value === '#^/foo%20bar/?$#';
        }

        return false;
      }))
      ->willReturn($processor_return);

    $prepare = new PreparePattern();
    $result = $prepare($input_pattern, $processor);

    $this->assertSame($expected, $result);
  }
}
