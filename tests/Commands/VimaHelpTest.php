<?php

namespace Vima\CodeIgniter\Tests\Commands;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Mock\MockInputOutput;
use Vima\CodeIgniter\Tests\VimaTestCase;

class VimaHelpTest extends VimaTestCase
{
    protected MockInputOutput $io;

    protected function setUp(): void
    {
        parent::setUp();

        $this->io = new MockInputOutput();
        CLI::setInputOutput($this->io);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::resetInputOutput();
    }
    public function testPolicyCreateHelp()
    {
        command('vima:policy create --help');

        $buffer = $this->io->getOutput();

        $this->assertStringContainsString('Vima Action Help - Policy Create', $buffer);
        $this->assertStringContainsString('Description:', $buffer);
        $this->assertStringContainsString('Generates a new Vima Policy file.', $buffer);
        $this->assertStringContainsString('Usage:', $buffer);
        $this->assertStringContainsString('php spark vima:policy create [name] [options]', $buffer);
        $this->assertStringContainsString('--resource', $buffer);
        $this->assertStringContainsString('--force', $buffer);
    }

    public function testRoleCreateHelp()
    {
        command('vima:role create --help');

        $buffer = $this->io->getOutput();

        $this->assertStringContainsString('Vima Action Help - Role Create', $buffer);
        $this->assertStringContainsString('Create a new role', $buffer);
        $this->assertStringContainsString('--parents', $buffer);
        $this->assertStringContainsString('--children', $buffer);
    }
}
