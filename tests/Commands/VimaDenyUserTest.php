<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Mock\MockInputOutput;
use Vima\Core\Permission\Entities\Permission;

class VimaDenyUserTest extends VimaTestCase
{
    public function testDenyUser()
    {
        $permRepo = service('vima_permissions');
        $perm = $permRepo->save(new Permission(name: 'forbidden.perm'));

        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        command('vima:user deny 1 forbidden.perm "Bad user"');

        $userDenyRepo = service('vima_user_denies');
        $this->assertTrue($userDenyRepo->isDenied(1, $perm->id));

        CLI::resetInputOutput();
    }
}
