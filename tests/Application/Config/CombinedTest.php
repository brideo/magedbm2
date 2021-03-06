<?php

namespace Meanbee\Magedbm2\Tests\Application\Config;

use Meanbee\Magedbm2\Application\Config\Combined;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

class CombinedTest extends TestCase
{
    /**
     * Test getting a value from the config.
     *
     * @test
     */
    public function testGet()
    {
        $app = new Application();
        $app->getDefinition()->addOption(new InputOption("test-option", null, InputOption::VALUE_REQUIRED));

        $input = new ArrayInput(["--test-option" => "test-value"]);

        $yaml = new Yaml();

        $config = new Combined($app, $input, $yaml);
        $input->bind($app->getDefinition());

        $this->assertEquals("test-value", $config->get("test-option"));
    }

    /**
     * Test setting a config value.
     *
     * @test
     */
    public function testSet()
    {
        $app = new Application();
        $input = new ArrayInput([]);
        $yaml = new Yaml();

        $config = new Combined($app, $input, $yaml);
        $input->bind($app->getDefinition());

        $config->set("test-option", "test-value");

        $this->assertEquals("test-value", $config->get("test-option"));
    }

    /**
     * Test loading configuration from a config file.
     *
     * @test
     */
    public function testLoadFromFile()
    {
        $app = new Application();
        $input = new ArrayInput([
            "--config" => implode(DIRECTORY_SEPARATOR, [__DIR__, "data", "config.yml"]),
        ]);
        $yaml = new Yaml();

        $config = new Combined($app, $input, $yaml);
        $input->bind($app->getDefinition());

        $this->assertEquals("test-value", $config->get("test-option"));
    }

    /**
     * Test that options specified on the console override options from the config file.
     *
     * @test
     */
    public function testConsoleOverride()
    {
        $app = new Application();
        $app->getDefinition()->addOption(new InputOption("test-option", null, InputOption::VALUE_REQUIRED));
        $input = new ArrayInput([
            "--config" => implode(DIRECTORY_SEPARATOR, [__DIR__, "data", "config.yml"]),
            "--test-option" => "overriden-value",
        ]);
        $yaml = new Yaml();

        $config = new Combined($app, $input, $yaml);
        $input->bind($app->getDefinition());

        $this->assertEquals("overriden-value", $config->get("test-option"));
    }
}
