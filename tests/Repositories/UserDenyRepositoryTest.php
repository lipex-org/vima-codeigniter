<?php

namespace Vima\CodeIgniter\Tests\Repositories;

use Vima\CodeIgniter\Repositories\UserDenyRepository;
use Vima\CodeIgniter\Repositories\PermissionRepository;
use Vima\Core\Permission\Entities\Permission;
use Vima\CodeIgniter\Tests\VimaTestCase;

class UserDenyRepositoryTest extends VimaTestCase
{
    protected UserDenyRepository $repository;
    protected PermissionRepository $permissionRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserDenyRepository();
        $this->permissionRepository = new PermissionRepository();

    }

    public function testAddAndIsDenied()
    {
        $perm = $this->permissionRepository->save(new Permission(name: 'denied.perm'));

        $this->assertFalse($this->repository->isDenied(1, $perm->id));

        $this->repository->add(1, $perm->id, 'Bad behavior');
        $this->assertTrue($this->repository->isDenied(1, $perm->id));
    }

    public function testRemoveDeny()
    {
        $perm = $this->permissionRepository->save(new Permission(name: 'denied.perm'));
        $this->repository->add(1, $perm->id);

        $this->assertTrue($this->repository->isDenied(1, $perm->id));

        $this->repository->remove(1, $perm->id);
        $this->assertFalse($this->repository->isDenied(1, $perm->id));
    }

    public function testGetDeniedPermissions()
    {
        $perm1 = $this->permissionRepository->save(new Permission(name: 'denied.1'));
        $perm2 = $this->permissionRepository->save(new Permission(name: 'denied.2'));

        $this->repository->add(1, $perm1->id, 'Reason 1');
        $this->repository->add(1, $perm2->id, 'Reason 2');

        $denies = $this->repository->getDeniedPermissions(1);
        $this->assertCount(2, $denies);
        $permission = $this->permissionRepository->findById($denies[0]->permissionId);

        $this->assertEquals('Reason 1', $denies[0]->reason);
        $this->assertEquals('denied.1', $permission->name);
    }
}
