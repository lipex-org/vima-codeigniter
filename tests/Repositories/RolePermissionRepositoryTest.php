<?php

namespace Vima\CodeIgniter\Tests\Repositories;

use Vima\CodeIgniter\Repositories\RolePermissionRepository;
use Vima\CodeIgniter\Repositories\RoleRepository;
use Vima\CodeIgniter\Repositories\PermissionRepository;
use Vima\Core\Role\Entities\Role;
use Vima\Core\Permission\Entities\Permission;
use Vima\Core\Role\Entities\RolePermission;
use Vima\CodeIgniter\Tests\VimaTestCase;

class RolePermissionRepositoryTest extends VimaTestCase
{
    protected RolePermissionRepository $repository;
    protected RoleRepository $roleRepository;
    protected PermissionRepository $permissionRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RolePermissionRepository();
        $this->roleRepository = new RoleRepository();
        $this->permissionRepository = new PermissionRepository();
    }

    public function testAssignAndGetRolePermissions()
    {
        $role = $this->roleRepository->save(new Role(name: 'editor'));
        $perm1 = $this->permissionRepository->save(new Permission(name: 'posts.create'));
        $perm2 = $this->permissionRepository->save(new Permission(name: 'posts.edit'));

        $this->repository->assign(new RolePermission(roleId: $role->id, permissionId: $perm1->id));
        $this->repository->assign(new RolePermission(roleId: $role->id, permissionId: $perm2->id));

        $rolePermissions = $this->repository->getRolePermissions($role);
        $this->assertCount(2, $rolePermissions);
    }

    public function testRevokePermission()
    {
        $role = $this->roleRepository->save(new Role(name: 'editor'));
        $perm = $this->permissionRepository->save(new Permission(name: 'posts.create'));
        $rolePerm = new RolePermission(roleId: $role->id, permissionId: $perm->id);

        $this->repository->assign($rolePerm);
        $this->assertCount(1, $this->repository->getRolePermissions($role));

        $this->repository->revoke($rolePerm);
        $this->assertCount(0, $this->repository->getRolePermissions($role));
    }

    public function testGetPermissionRoles()
    {
        $role1 = $this->roleRepository->save(new Role(name: 'admin'));
        $role2 = $this->roleRepository->save(new Role(name: 'editor'));
        $perm = $this->permissionRepository->save(new Permission(name: 'shared.perm'));

        $this->repository->assign(new RolePermission(roleId: $role1->id, permissionId: $perm->id));
        $this->repository->assign(new RolePermission(roleId: $role2->id, permissionId: $perm->id));

        $permissionRoles = $this->repository->getPermissionRoles($perm);
        $this->assertCount(2, $permissionRoles);
    }
}
