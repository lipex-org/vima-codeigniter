<?php

namespace Vima\CodeIgniter\Tests\Commands;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Mock\MockInputOutput;
use Vima\CodeIgniter\Tests\VimaTestCase;
use Vima\Core\Role\Entities\Role;
use Vima\Core\Permission\Entities\Permission;
use Vima\Core\Vima;

class VimaMapsCommandTest extends VimaTestCase
{
    private string $vimaDir;
    private string $mappingFile;
    private string $rolesFile;
    private string $permsFile;
    protected MockInputOutput $io;

    protected function setUp(): void
    {
        parent::setUp();

        parent::setUp();

        $this->io = new MockInputOutput();
        CLI::setInputOutput($this->io);

        $this->vimaDir = APPPATH . '../.vima/';
        $this->mappingFile = $this->vimaDir . 'mapping.json';
        $this->rolesFile = APPPATH . 'Mappers/Vima/Roles.php';
        $this->permsFile = APPPATH . 'Mappers/Vima/Permissions.php';

        if (!is_dir($this->vimaDir)) {
            mkdir($this->vimaDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up files created during mapping test
        if (file_exists($this->mappingFile)) {
            unlink($this->mappingFile);
        }
        if (is_dir($this->vimaDir)) {
            @rmdir($this->vimaDir);
        }

        if (file_exists($this->rolesFile)) {
            unlink($this->rolesFile);
        }
        if (file_exists($this->permsFile)) {
            unlink($this->permsFile);
        }

        parent::tearDown();

        CLI::resetInputOutput();
    }

    public function testMapsGenerationIncludesDatabaseItems()
    {
        // Create database role & permission not defined in setup config
        $roleRepo = service('vima_roles');
        $permRepo = service('vima_permissions');

        $roleRepo->save(new Role(name: 'db_only_role', description: 'DB Only Role'));
        $permRepo->save(new Permission(name: 'db_only_perm', description: 'DB Only Perm'));

        // Run map generation
        command('vima:maps generate');

        $this->assertFileExists($this->mappingFile);
        $this->assertFileExists($this->rolesFile);
        $this->assertFileExists($this->permsFile);

        $rolesContent = file_get_contents($this->rolesFile);
        $permsContent = file_get_contents($this->permsFile);

        // Verify database-defined items are generated in PHP mappers
        $this->assertStringContainsString("DB_ONLY_ROLE = 'db_only_role'", $rolesContent);
        $this->assertStringContainsString("DB_ONLY_PERM = 'db_only_perm'", $permsContent);
    }
}
