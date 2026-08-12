<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Repositories\PermissionRepository;
use Vima\CodeIgniter\Repositories\UserPermissionRepository;
use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Mock\MockInputOutput;
use Vima\Core\Permission\Entities\Permission;

class VimaPermitUserTest extends VimaTestCase
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

    public function testPermitUser()
    {
        /**
         * @var PermissionRepository $permRepo
         */
        $permRepo = service('vima_permissions');
        $permRepo->save(new Permission(name: 'extra.perm'));

        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        command('vima:user permit 1 extra.perm');

        /**
         * @var UserPermissionRepository 
         */
        $userPermRepo = service('vima_user_permissions');
        $userPerms = $userPermRepo->findByUserId(1);
        $perm = $permRepo->findById($userPerms[0]->permissionId);

        $this->assertCount(1, $userPerms);
        $this->assertEquals('extra.perm', $perm->name);

        CLI::resetInputOutput();
    }
}
