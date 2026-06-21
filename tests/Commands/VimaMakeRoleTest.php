<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Mock\MockInputOutput;

class VimaMakeRoleTest extends VimaTestCase
{
    public function testMakeRoleCreatesRoleInDatabase()
    {
        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        command('vima:role create editor "Editor role"');

        $roleRepo = service('vima_roles');
        $role = $roleRepo->findByName('editor');

        $this->assertNotNull($role);
        $this->assertEquals('editor', $role->name);
        $this->assertEquals('Editor role', $role->description);

        CLI::resetInputOutput();
    }
}
