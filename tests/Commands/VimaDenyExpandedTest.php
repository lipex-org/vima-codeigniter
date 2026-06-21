<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Mock\MockInputOutput;
use Vima\Core\Permission\Entities\Permission;
use Vima\Core\Role\Entities\Role;

class VimaDenyExpandedTest extends VimaTestCase
{
    public function testDenyRole()
    {
        $roleRepo = service('vima_roles');
        $role = $roleRepo->save(new Role(name: 'test_role'));

        $io = new MockInputOutput();
        $io->setInputs(['1', 'test_role', 'Bad role']);
        CLI::setInputOutput($io);

        command('vima:user deny --role yes');

        $userRoleDenyRepo = service('vima_user_role_denies');
        $this->assertTrue($userRoleDenyRepo->isDenied(1, $role->id));

        CLI::resetInputOutput();
    }

    public function testTemporalDeny()
    {
        $permRepo = service('vima_permissions');
        $perm = $permRepo->save(new Permission(name: 'temp.perm'));

        $io = new MockInputOutput();
        $io->setInputs(['1', 'temp.perm', 'Temp deny']);
        CLI::setInputOutput($io);

        command('vima:user deny --for "1 hour"');

        $userDenyRepo = service('vima_user_denies');
        $this->assertTrue($userDenyRepo->isDenied(1, $perm->id));

        // Manual check of expiration
        $denies = $userDenyRepo->getDeniedPermissions(1);
        $this->assertCount(1, $denies);
        $this->assertNotNull($denies[0]->expiresAt);

        CLI::resetInputOutput();
    }
}
