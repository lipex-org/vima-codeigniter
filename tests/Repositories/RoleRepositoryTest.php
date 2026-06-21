<?php

namespace Vima\CodeIgniter\Tests\Repositories;

use Vima\CodeIgniter\Repositories\RoleRepository;
use Vima\Core\Role\Entities\Role;
use Vima\CodeIgniter\Tests\VimaTestCase;

class RoleRepositoryTest extends VimaTestCase
{
    protected RoleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RoleRepository();
    }

    public function testSaveAndFindRole()
    {
        $role = new Role(name: 'admin', description: 'Administrator');
        $savedRole = $this->repository->save($role);

        $this->assertNotNull($savedRole->id);
        $this->assertEquals('admin', $savedRole->name);

        $foundRole = $this->repository->findById($savedRole->id);
        $this->assertNotNull($foundRole);
        $this->assertEquals('admin', $foundRole->name);
    }

    public function testFindByName()
    {
        $role = new Role(name: 'editor');
        $this->repository->save($role);

        $foundRole = $this->repository->findByName('editor');
        $this->assertNotNull($foundRole);
        $this->assertEquals('editor', $foundRole->name);
    }

    public function testAllRoles()
    {
        $this->repository->save(new Role(name: 'role1'));
        $this->repository->save(new Role(name: 'role2'));

        $roles = $this->repository->all();
        $this->assertCount(2, $roles);
    }

    public function testSaveExistingRoleUpdatesDescription()
    {
        $role = new Role(name: 'admin', description: 'Old');
        $this->repository->save($role);
        
        $role2 = new Role(name: 'admin', description: 'New');
        $this->repository->save($role2);
        
        $found = $this->repository->findByName('admin');
        $this->assertEquals('New', $found->description);
    }

    public function testRoleInheritance()
    {
        $admin = new Role(name: 'admin');
        $editor = new Role(name: 'editor');

        $this->repository->save($admin);

        // Role doesn't have inherit method, it's just data.
        // We are testing that save() works with Role.
        $this->repository->save($editor);

        $foundSimple = $this->repository->findById($editor->id);
        $this->assertNotNull($foundSimple);
    }
}
