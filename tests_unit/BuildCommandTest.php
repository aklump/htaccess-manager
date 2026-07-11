<?php

namespace AKlump\HtaccessManager\Tests\Unit;

use AKlump\HtaccessManager\BuildCommand;
use AKlump\HtaccessManager\Tests\Unit\TestingTraits\TestWithFilesTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Command\Command;

use Symfony\Component\Yaml\Yaml;

/**
 * @covers \AKlump\HtaccessManager\BuildCommand
 * @uses \AKlump\HtaccessManager\Config\LoadConfig
 * @uses \AKlump\HtaccessManager\Helper\GetShortPath
 * @uses \AKlump\HtaccessManager\Helper\PrepareOutputPath
 * @uses \AKlump\HtaccessManager\Helper\RemoveComments
 * @uses \AKlump\HtaccessManager\Helper\SplitHeader
 * @uses \AKlump\HtaccessManager\Output\Icons
 * @uses \AKlump\HtaccessManager\Config\Defaults
 * @uses \AKlump\HtaccessManager\Helper\SubstituteEnvVars
 * @uses \AKlump\HtaccessManager\Plugin\MergePluginSchemas
 * @uses \AKlump\HtaccessManager\Config\NormalizeConfig
 */
class BuildCommandTest extends TestCase {

  use TestWithFilesTrait;

  public function testExecuteBuildsFile() {
    $config_path = $this->getTestFileFilepath('BuildCommandTest/config.yml', TRUE);
    $output_path = $this->getTestFileFilepath('BuildCommandTest/.htaccess');

    $config = [
      'files' => [
        'main' => [
          'title' => 'Main',
          'valid_hosts' => ['https://example.com'],
          'output' => [$output_path],
        ],
      ],
    ];
    file_put_contents($config_path, Yaml::dump($config));

    $command = new BuildCommand('https://example.com', []);
    $input = new ArrayInput(['configuration' => $config_path]);
    $output = new BufferedOutput();

    $result = $command->run($input, $output);

    $this->assertEquals(Command::SUCCESS, $result);
    $this->assertFileExists($output_path);
    $this->assertStringContainsString('.htaccess', $output->fetch());
  }

  public function testExecuteWithMultipleOutputs() {
    $config_path = $this->getTestFileFilepath('BuildCommandTest/config_multi.yml', TRUE);
    $output_path1 = $this->getTestFileFilepath('BuildCommandTest/.htaccess1');
    $output_path2 = $this->getTestFileFilepath('BuildCommandTest/.htaccess2');

    $config = [
      'files' => [
        'main' => [
          'title' => 'Main',
          'valid_hosts' => ['https://example.com'],
          'output' => [$output_path1, $output_path2],
        ],
      ],
    ];
    file_put_contents($config_path, Yaml::dump($config));

    $command = new BuildCommand('https://example.com', []);
    $input = new ArrayInput(['configuration' => $config_path]);
    $output = new BufferedOutput();

    $result = $command->run($input, $output);

    $this->assertEquals(Command::SUCCESS, $result);
    $this->assertFileExists($output_path1);
    $this->assertFileExists($output_path2);
    $content1 = file_get_contents($output_path1);
    $content2 = file_get_contents($output_path2);
    $this->assertEquals($content1, $content2);
  }

  public function testExecuteWithRemoveComments() {
    $config_path = $this->getTestFileFilepath('BuildCommandTest/config_comments.yml', TRUE);
    $output_path = $this->getTestFileFilepath('BuildCommandTest/.htaccess_comments');

    $config = [
      'files' => [
        'main' => [
          'title' => 'Main',
          'valid_hosts' => ['https://example.com'],
          'output' => [$output_path],
          'remove_comments' => TRUE,
        ],
      ],
    ];
    file_put_contents($config_path, Yaml::dump($config));

    // We need a plugin or something to add comments,
    // or just rely on the fact that FileHeader (if it were a plugin) might add them.
    // BuildCommand applies plugins. Let's mock a plugin that adds comments.

    // Actually, BuildCommand::applyPlugins uses $this->plugins which is an array of plugin info.
    // It instantiates them: new $plugin['classname']()

    $plugin_class = get_class(new class {
        public function __invoke($resource, $config, $context) {
            fwrite($resource, "# Header\n\n# Comment\nRewriteEngine On\n");
        }
    });

    $command = new BuildCommand('https://example.com', [['classname' => $plugin_class]]);
    $input = new ArrayInput(['configuration' => $config_path]);
    $output = new BufferedOutput();

    $result = $command->run($input, $output);

    $this->assertEquals(Command::SUCCESS, $result);
    $content = file_get_contents($output_path);
    // RemoveComments should remove "# Comment" but keep "# Header" (if it's in the header)
    // Actually SplitHeader defines what is header.
    $this->assertStringContainsString('RewriteEngine On', $content);
    $this->assertStringNotContainsString('# Comment', $content);
  }

  public function testExecuteFailsOnInvalidContent() {
    $config_path = $this->getTestFileFilepath('BuildCommandTest/config_fail.yml', TRUE);
    $output_path = $this->getTestFileFilepath('BuildCommandTest/.htaccess_fail');

    $config = [
      'files' => [
        'main' => [
          'title' => 'Main',
          'valid_hosts' => ['https://example.com'],
          'output' => [$output_path],
          'remove_comments' => TRUE,
        ],
      ],
    ];
    file_put_contents($config_path, Yaml::dump($config));

    // A plugin that writes content without double newline,
    // causing SplitHeader to throw RuntimeException.
    $plugin_class = get_class(new class {
      public function __invoke($resource, $config, $context) {
        fwrite($resource, "# No Double Newline\nBody Content");
      }
    });

    $command = new BuildCommand('https://example.com', [['classname' => $plugin_class]]);
    $input = new ArrayInput(['configuration' => $config_path]);
    $output = new BufferedOutput();

    $result = $command->run($input, $output);

    $this->assertEquals(Command::FAILURE, $result);
    $this->assertStringContainsString('Failed to remove comments', $output->fetch());
  }

  protected function setUp(): void {
    parent::setUp();
    $this->getTestFileFilepath('BuildCommandTest/', TRUE);
  }

  protected function tearDown(): void {
    $this->deleteTestFile('BuildCommandTest/');
    parent::tearDown();
  }
}
