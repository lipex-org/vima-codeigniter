<?php

namespace Vima\CodeIgniter\Tests\Repositories;

use Vima\CodeIgniter\Repositories\RoleParentRepository;
use Vima\CodeIgniter\Repositories\RoleRepository;
use Vima\Core\Role\Entities\Role;
use Vima\Core\Role\Entities\RoleParent;
use Vima\CodeIgniter\Tests\VimaTestCase;

class RoleParentRepositoryTest extends VimaTestCase
{
    protected RoleParentRepository $repository;
    protected RoleRepository $roleRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RoleParentRepository();
        $this->roleRepository = new RoleRepository();
    }

    public function testAssignAndGetParents()
    {
        $child = $this->roleRepository->save(new Role(name: 'editor'));
        $parent = $this->roleRepository->save(new Role(name: 'viewer'));

        $this->repository->assign(new RoleParent(roleId: $child->id, parentId: $parent->id));

        $parents = $this->repository->getParents($child);
        $rParent = $this->roleRepository->findById($parents[0]->parentId);

        $this->assertCount(1, $parents);
        $this->assertEquals($parent->id, $rParent->id);
    }

    public function testGetChildren()
    {
        $child = $this->roleRepository->save(new Role(name: 'editor'));
        $parent = $this->roleRepository->save(new Role(name: 'viewer'));

        $this->repository->assign(new RoleParent(roleId: $child->id, parentId: $parent->id));

        $children = $this->repository->getChildren($parent);
        $this->assertCount(1, $children);
        $this->assertEquals($child->id, $children[0]->id);
    }

    public function testRevokeParent()
    {
        $child = $this->roleRepository->save(new Role(name: 'editor'));
        $parent = $this->roleRepository->save(new Role(name: 'viewer'));
        $roleParent = new RoleParent(roleId: $child->id, parentId: $parent->id);

        $this->repository->assign($roleParent);
        $this->assertCount(1, $this->repository->getParents($child));

        $this->repository->remove($roleParent);
        $this->assertCount(0, $this->repository->getParents($child));
    }
}
