<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Repositories\PermissionRepository;
use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Mock\MockInputOutput;
use Vima\Core\Permission\Entities\Permission;

class VimaUndenyUserTest extends VimaTestCase
{
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

        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        command('vima:user undeny 1 forbidden.perm');

        $this->assertFalse($userDenyRepo->isDenied(1, $perm->id));

        CLI::resetInputOutput();
    }
}
