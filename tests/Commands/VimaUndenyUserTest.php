<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Repositories\PermissionRepository;
use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Mock\MockInputOutput;
use Vima\Core\Permission\Entities\Permission;

class VimaUndenyUserTest extends VimaTestCase
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

    public function testUndenyUser()
    {
        /**
         * @var PermissionRepository
         */
        $permRepo = service('vima_permissions');
        $perm = $permRepo->save(new Permission(name: 'forbidden.perm'));

        $userDenyRepo = service('vima_user_denies');
        $userDenyRepo->add(1, $perm->id, 'Bad user');

        $this->assertTrue($userDenyRepo->isDenied(1, $perm->id));

        command('vima:user undeny 1 forbidden.perm');

        $this->assertFalse($userDenyRepo->isDenied(1, $perm->id));
    }
}
