<?php

namespace Vima\CodeIgniter\Tests\Integration;

use Vima\CodeIgniter\Tests\VimaTestCase;
use Vima\Core\Vima;
use Vima\Core\Role\Entities\Role;
use Vima\Core\Permission\Entities\Permission;

class BulkOperationsTest extends VimaTestCase
{
    private object $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new class implements \Vima\Core\User\Contracts\UserInterface {
            public int $id = 42;
            public function vimaGetId(): string|int { return $this->id; }
        };
    }

    public function testRoleBulkPermissionsIntegration()
    {
        $role = Vima::roles()->save(new Role('ci_editor'));
        $perm1 = Vima::permissions()->create('ci.posts.edit');
        $perm2 = Vima::permissions()->create('ci.posts.create');

        Vima::role('ci_editor')
            ->permissions()
            ->add(['ci.posts.edit', $perm2]);

        $perms = Vima::role('ci_editor')->permissions()->all();
        $this->assertCount(2, $perms);

        Vima::role('ci_editor')->permissions()->remove(['ci.posts.edit', 'ci.posts.create']);
        $this->assertEmpty(Vima::role('ci_editor')->permissions()->all());
    }

    public function testUserBulkRolesAndPermissionsIntegration()
    {
        Vima::roles()->save(new Role('ci_admin'));
        Vima::roles()->save(new Role('ci_manager'));
        Vima::permissions()->create('ci.users.manage');
        Vima::permissions()->create('ci.users.view');

        // Bulk Grant
        Vima::user($this->user)->grant()->role(['ci_admin', 'ci_manager']);
        Vima::user($this->user)->grant()->permission(['ci.users.manage', 'ci.users.view' => ['scope' => 'ci']]);

        $roles = Vima::user($this->user)->get()->roles();
        $this->assertCount(2, $roles);

        $perms = Vima::user($this->user)->get()->permissions()->direct();
        $this->assertCount(2, $perms);

        // Bulk Revoke
        Vima::user($this->user)->revoke()->role(['ci_admin', 'ci_manager']);
        Vima::user($this->user)->revoke()->permission(['ci.users.manage', 'ci.users.view']);

        $this->assertEmpty(Vima::user($this->user)->get()->roles());
        $this->assertEmpty(Vima::user($this->user)->get()->permissions()->direct());
    }
}
