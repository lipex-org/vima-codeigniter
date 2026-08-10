<?php

namespace Vima\CodeIgniter\Tests\Traits;

use CodeIgniter\Config\Factories;
use Vima\CodeIgniter\Repositories\PermissionRepository;
use Vima\CodeIgniter\Repositories\RoleRepository;
use Vima\CodeIgniter\Repositories\UserRoleRepository;
use Vima\CodeIgniter\Tests\VimaTestCase;
use Vima\CodeIgniter\Traits\VimaTrait;
use Vima\CodeIgniter\Tests\Fixtures\User;
use Vima\Core\Cache\Contracts\CacheInterface;
use Vima\Core\Permission\Entities\Permission;
use Vima\Core\Role\Entities\Role;
use Vima\Core\Role\Entities\RolePermission;
use Vima\Core\User\Entities\UserRole;
use Vima\Core\Exceptions\AccessDeniedExceptionInterface;
use function Vima\Core\resolve;

class VimaTraitTest extends VimaTestCase
{
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        helper('vima');
        resolve(CacheInterface::class)->clear();

        $this->controller = new class {
            use VimaTrait;

            // Expose protected methods for testing
            public function testCan(string $permission, ...$arguments): bool
            {
                return $this->can($permission, ...$arguments);
            }

            public function testCanAny(array $permissions, ...$arguments): bool
            {
                return $this->can_any($permissions, ...$arguments);
            }

            public function testCanAll(array $permissions, ...$arguments): bool
            {
                return $this->can_all($permissions, ...$arguments);
            }

            public function testAuthorize(string $permission, ...$arguments): void
            {
                $this->authorize($permission, ...$arguments);
            }

            public function testAuthorizeAny(array $permissions, ...$arguments): void
            {
                $this->authorize_any($permissions, ...$arguments);
            }

            public function testAuthorizeAll(array $permissions, ...$arguments): void
            {
                $this->authorize_all($permissions, ...$arguments);
            }

            public function testDenyUser(string $permission, ?string $reason = null): void
            {
                $this->denyUser($permission, $reason);
            }

            public function testIsDenied(string $permission): bool
            {
                return $this->isDenied($permission);
            }
        };

        // Setup mock user
        $mockUser = new User(1);
        $config = config('Vima');
        $config->cache['enabled'] = false; // Disable cache for this test
        $config->user['current'] = function () use ($mockUser) {
            return $mockUser;
        };

        Factories::injectMock('config', 'Vima', $config);

        // Populate database with permissions and roles
        /**
         * @var PermissionRepository
         */
        $permRepo = service('vima_permissions');
        /**
         * @var RoleRepository
         */
        $roleRepo = service('vima_roles');
        /**
         * @var UserRoleRepository
         */
        $userRoleRepo = service('vima_user_roles');
        $rolePermRepo = service('vima_role_permissions');

        $editPerm = $permRepo->save(new Permission(name: 'edit.post'));
        $viewPerm = $permRepo->save(new Permission(name: 'view.post'));

        $role = $roleRepo->save(new Role(name: 'editor'));

        $rolePermRepo->assign(new RolePermission(
            roleId: $role->id,
            permissionId: $editPerm->id
        ));
        $rolePermRepo->assign(new RolePermission(
            roleId: $role->id,
            permissionId: $viewPerm->id
        ));

        $userRoleRepo->assign(new UserRole(userId: 1, roleId: $role->id));
    }

    public function testCan()
    {
        $this->assertTrue($this->controller->testCan('edit.post'));
        $this->assertFalse($this->controller->testCan('delete.post'));
    }

    public function testCanAny()
    {
        $this->assertTrue($this->controller->testCanAny(['edit.post', 'delete.post']));
        $this->assertTrue($this->controller->testCanAny(['delete.post', 'edit.post']));
        $this->assertFalse($this->controller->testCanAny(['delete.post', 'other.post']));
    }

    public function testCanAll()
    {
        $this->assertTrue($this->controller->testCanAll(['edit.post']));
        $this->assertFalse($this->controller->testCanAll(['delete.post']));
        $this->assertFalse($this->controller->testCanAll(['edit.post', 'delete.post']));
    }

    public function testAuthorize()
    {
        $this->controller->testAuthorize('edit.post'); // Should not throw exception

        $this->expectException(AccessDeniedExceptionInterface::class);
        $this->controller->testAuthorize('delete.post');
    }

    public function testAuthorizeAny()
    {
        $this->controller->testAuthorizeAny(['edit.post', 'delete.post']); // Should not throw

        $this->expectException(AccessDeniedExceptionInterface::class);
        $this->controller->testAuthorizeAny(['delete.post', 'other.post']);
    }

    public function testAuthorizeAll()
    {
        $this->controller->testAuthorizeAll(['edit.post', 'view.post']); // Should not throw

        $this->expectException(AccessDeniedExceptionInterface::class);
        $this->controller->testAuthorizeAll(['edit.post', 'delete.post']);
    }


    public function testDenyUserAndIsDenied()
    {
        $this->assertFalse($this->controller->testIsDenied('edit.post'));
        $this->controller->testDenyUser('edit.post', 'No edits for you');
        $this->assertTrue($this->controller->testIsDenied('edit.post'));

        // Check that the permission is now actually denied via can()
        $this->assertFalse($this->controller->testCan('edit.post'));
    }
}
