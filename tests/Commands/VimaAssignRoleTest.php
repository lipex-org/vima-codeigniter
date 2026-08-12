<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Models\UserRoleModel;
use Vima\CodeIgniter\Repositories\RoleRepository;
use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Mock\MockInputOutput;
use Vima\Core\Role\Entities\Role;

class VimaAssignRoleTest extends VimaTestCase
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

    public function testAssignRoleToUser()
    {
        /**
         * @var RoleRepository
         */
        $roleRepo = service('vima_roles');
        $roleRepo->save(new Role(name: 'admin'));

        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        command('vima:role assign 1 admin');

        $userRoleRepo = service('vima_user_roles');
        $roles = $userRoleRepo->getRolesForUser(1);
        $role = $roleRepo->findById($roles[0]->roleId);

        $this->assertCount(1, $roles);
        $this->assertEquals('admin', $role->name);

        CLI::resetInputOutput();
    }
}
