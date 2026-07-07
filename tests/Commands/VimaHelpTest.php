<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\Test\StreamFilterTrait;

class VimaHelpTest extends VimaTestCase
{
    use StreamFilterTrait;

    public function testPolicyCreateHelp()
    {
        command('vima:policy create --help');

        $buffer = $this->getStreamFilterBuffer();

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

        $buffer = $this->getStreamFilterBuffer();

        $this->assertStringContainsString('Vima Action Help - Role Create', $buffer);
        $this->assertStringContainsString('Create a new role', $buffer);
        $this->assertStringContainsString('--parents', $buffer);
        $this->assertStringContainsString('--children', $buffer);
    }
}
