<?php

declare(strict_types=1);

/*
 * This file is part of the box project.
 *
 * (c) Kevin Herrera <kevin@herrera.io>
 *     Théo Fidry <theo.fidry@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace KevinGH\Box\Test;

use KevinGH\Box\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @private
 */
abstract class CommandTestCase extends FileSystemTestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var string the name of the command
     */
    private $name;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application();

        $this->name = $this->getCommand()->getName();

        $this->application->add($this->getCommand());
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->application = null;
    }

    /**
     * Returns the command to be tested.
     *
     * @return Command the command
     */
    abstract protected function getCommand(): Command;

    /**
     * Returns the output for the tester.
     *
     * @param CommandTester $tester the tester
     *
     * @return string the output
     */
    protected function getOutput(CommandTester $tester)
    {
        /** @var $output StreamOutput */
        $output = $tester->getOutput();
        $stream = $output->getStream();
        $string = '';

        rewind($stream);

        while (false === feof($stream)) {
            $string .= fgets($stream);
        }

        $string = preg_replace(
            [
                '/\x1b(\[|\(|\))[;?0-9]*[0-9A-Za-z]/',
                '/[\x03|\x1a]/',
            ],
            ['', '', ''],
            $string
        );

        return str_replace(PHP_EOL, "\n", $string);
    }

    protected function getCommandTester(): CommandTester
    {
        return new CommandTester($this->application->get($this->name));
    }
}
