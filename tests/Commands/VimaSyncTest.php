<?php

declare(strict_types=1);

namespace Vima\CodeIgniter\Tests\Commands;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Mock\MockInputOutput;
use Config\Services;
use Vima\CodeIgniter\Commands\VimaSync;
use Vima\CodeIgniter\Tests\Fixtures\Setup;
use Vima\CodeIgniter\Tests\VimaTestCase;
use Vima\Core\Role\Entities\Role;
use Vima\Core\Vima;

final class VimaSyncTest extends VimaTestCase
{

    protected MockInputOutput $io;

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::resetInputOutput();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->io = new MockInputOutput();
        CLI::setInputOutput($this->io);
        $setup = new Setup();

        $config = service('vima_config');

        $config->setup->roles = $setup->get()['roles'];
        $config->setup->permissions = $setup->get()['permissions'];

        Services::injectMock('vima_config', $config);

        $io = new MockInputOutput();

        CLI::setInputOutput($io);

        command('vima:sync');

        CLI::resetInputOutput();
    }

    public function test_sync_service(): void
    {
        $this->assertTrue(true);

        $roles = Vima::roles()->all();

        $this->assertCount(1, $roles);
        $this->assertEquals('admin', $roles[0]->name);
        $this->assertEquals('can access everything', $roles[0]->description);

        $rolePerms = Vima::role($roles[0])->permissions()->all();
        $this->assertCount(1, $rolePerms);
        $this->assertEquals('test.view', $rolePerms[0]->name);
        $this->assertEquals('This is a permssion', $rolePerms[0]->description);
    }

    public function test_sync_service_with_refresh(): void
    {
        // Add a dummy role to ensure it gets wiped out by refresh
        $roleRepo = service('vima_roles');
        $roleRepo->save(new Role(name: 'dummy_role'));

        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        $command = new VimaSync(service('logger'), service('commands'));
        $command->run(['refresh' => true]);

        CLI::resetInputOutput();

        $roles = Vima::roles()->all();

        // Ensure the dummy role is gone, leaving only the 'admin' from config
        $this->assertCount(1, $roles);
        $this->assertEquals('admin', $roles[0]->name);
    }
}
