<?php

namespace AKlump\HtaccessManager\Tests\Unit\Plugin\Helper;

use AKlump\HtaccessManager\Helper\PrepareOutputPath;
use AKlump\HtaccessManager\Tests\Unit\TestingTraits\TestWithFilesTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\HtaccessManager\Helper\PrepareOutputPath
 */
class PrepareOutputPathTest extends TestCase {

  use TestWithFilesTrait;

  public function testInvoke() {
    $this->deleteTestFile('.cache');
    $output_filepath = $this->getTestFileFilepath('.cache/output/lorem');
    $this->assertDirectoryDoesNotExist(dirname($output_filepath));
    (new PrepareOutputPath())($output_filepath);

    $directory = dirname($output_filepath);
    $this->assertDirectoryExists(dirname($directory));
    $this->assertDirectoryIsWritable($directory);

    chmod($directory, 0444);

    $this->assertDirectoryIsNotWritable($directory);
    (new PrepareOutputPath())($output_filepath);
    $this->assertDirectoryIsWritable($directory);

    $this->deleteTestFile($output_filepath);
  }
}
