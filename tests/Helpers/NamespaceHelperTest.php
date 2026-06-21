<?php

namespace Vima\CodeIgniter\Tests\Helpers;

use Vima\CodeIgniter\Tests\VimaTestCase;
use Vima\CodeIgniter\Tests\Fixtures\User;
use Vima\Core\Permission\Entities\Permission;
use Vima\Core\Role\Entities\Role;
use Vima\Core\Vima;

class NamespaceHelperTest extends VimaTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Setup mock user for 'can' helper
        $mockUser = new User(1);
        $config = config('Vima');
        $config->user['current'] = function () use ($mockUser) {
            return $mockUser;
        };
    }

    public function testCanResolvesNamespaceFromPermissionString()
    {
        $user = config('Vima')->getCurrentUser();

        // Define namespaced permission
        $p = Permission::define("edit", namespace: "blog");
        $savedPermission = Vima::permissions()->save($p);

        $role = Role::define(name: "editor", namespace: "blog");
        $savedRole = Vima::roles()->save($role);

        Vima::role($savedRole)->permissions()->add($savedPermission);
        Vima::user($user)->grant()->role($savedRole);

        // Test with blog:edit
        $this->assertTrue(can('blog:edit'));

        // Test with other:edit (should fail)
        $this->assertFalse(can('other:edit'));
    }

    public function testCanUsesNamespaceAsSecondArgument()
    {
        $user = config('Vima')->getCurrentUser();

        $p = Permission::define("edit", namespace: "admin");
        $savedPermission = Vima::permissions()->save($p);

        $role = Role::define(name: "super", namespace: "admin");
        $savedRole = Vima::roles()->save($role);

        Vima::role($savedRole)->permissions()->add($savedPermission);
        Vima::user($user)->grant()->role($savedRole);

        // Test passing namespace explicitly
        $this->assertTrue(can('edit', 'admin'));

        // Test passing wrong namespace
        $this->assertFalse(can('edit', 'standard'));
    }
}
