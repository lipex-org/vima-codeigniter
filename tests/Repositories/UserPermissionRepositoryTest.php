<?php

namespace Vima\CodeIgniter\Tests\Repositories;

use Vima\CodeIgniter\Repositories\UserPermissionRepository;
use Vima\CodeIgniter\Repositories\PermissionRepository;
use Vima\Core\Permission\Entities\Permission;
use Vima\Core\User\Entities\UserPermission;
use Vima\CodeIgniter\Tests\VimaTestCase;

class UserPermissionRepositoryTest extends VimaTestCase
{
    protected UserPermissionRepository $repository;
    protected PermissionRepository $permissionRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserPermissionRepository();
        $this->permissionRepository = new PermissionRepository();
    }

    public function testAssignAndGetPermissionsForUser()
    {
        $perm1 = $this->permissionRepository->save(new Permission(name: 'extra.perm1'));
        $perm2 = $this->permissionRepository->save(new Permission(name: 'extra.perm2'));

        $this->repository->add(new UserPermission(userId: 1, permissionId: $perm1->id));
        $this->repository->add(new UserPermission(userId: 1, permissionId: $perm2->id));

        $permissions = $this->repository->findByUserId(1);
        $this->assertCount(2, $permissions);
    }

    public function testRevokePermission()
    {
        $perm = $this->permissionRepository->save(new Permission(name: 'extra.perm'));
        $userPerm = new UserPermission(userId: 1, permissionId: $perm->id);

        $this->repository->add($userPerm);
        $this->assertCount(1, $this->repository->findByUserId(1));

        $this->repository->remove($userPerm);
        $this->assertCount(0, $this->repository->findByUserId(1));
    }
}
