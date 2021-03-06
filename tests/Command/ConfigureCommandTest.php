<?php

namespace Meanbee\Magedbm2\Tests\Command;

use Meanbee\Magedbm2\Application\ConfigInterface;
use Meanbee\Magedbm2\Command\ConfigureCommand;
use Meanbee\Magedbm2\Service\FilesystemInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;

class ConfigureCommandTest extends TestCase
{
    /**
     * Test that the command saves the configuration correctly in interactive mode.
     *
     * @test
     */
    public function testInteractive()
    {
        $filesystem = $this->createMock(FilesystemInterface::class);
        $filesystem
            ->expects($this->once())
            ->method("write")
            ->with(
                $this->equalTo("/tmp/test-config-file.yml"),
                $this->equalTo("test-option: test-option-value\nother-option: other-option-value\n")
            );

        $tester = $this->getCommandTester($filesystem);
        $tester->execute([], ["interactive" => true]);
    }

    /**
     * Test that the command saves the configuration correctly in non-interactive mode.
     *
     * @test
     */
    public function testNonInteractive()
    {
        $filesystem = $this->createMock(FilesystemInterface::class);
        $filesystem
            ->expects($this->once())
            ->method("write")
            ->with(
                $this->equalTo("/tmp/test-config-file.yml"),
                $this->equalTo("test-option: default-test-option-value\nother-option: null\n")
            );

        $tester = $this->getCommandTester($filesystem);
        $tester->execute([], ["interactive" => false]);
    }

    /**
     * Create and configure a tester for the "configure" command.
     *
     * @param FilesystemInterface $filesystem
     *
     * @return CommandTester
     */
    protected function getCommandTester($filesystem)
    {
        // Create an input definition mock to provide available config options
        $definition = new InputDefinition([
            new InputOption("test-option", null, InputOption::VALUE_REQUIRED, "Test Option"),
            new InputOption("other-option", null, InputOption::VALUE_REQUIRED, "Other Option"),
        ]);

        // Create a question helper to provide responses to interactive input prompts
        $question_helper = $this->createMock(QuestionHelper::class);
        $question_helper
            ->method("ask")
            ->willReturnCallback(function ($input, $output, $question) {
                /** @var Question $question */
                switch ($question->getQuestion()) {
                    case "Test Option: ":
                        return "test-option-value";
                    case "Other Option: ":
                        return "other-option-value";
                    default:
                        throw new \Exception("Unexpected interactive prompt!");
                }
            });

        // Create an application mock to provide input definition and helper set
        $application = $this->createMock(Application::class);
        $application
            ->method("getDefinition")
            ->willReturn($definition);
        $application
            ->method("getHelperSet")
            ->willReturn(new HelperSet([
                "question" => $question_helper,
            ]));

        // Create a config mock to return the config file path and default config values
        $config = $this->createMock(ConfigInterface::class);
        $config
            ->method("get")
            ->willReturnCallback(function ($option) {
                switch ($option) {
                    case "test-option":
                        return "default-test-option-value";
                    default:
                        return null;
                }
            });
        $config
            ->method("getConfigFile")
            ->willReturn("/tmp/test-config-file.yml");

        $command = new ConfigureCommand($config, $filesystem, new Yaml());
        $command->setApplication($application);

        $tester = new CommandTester($command);

        return $tester;
    }
}
