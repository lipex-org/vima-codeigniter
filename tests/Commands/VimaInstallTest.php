<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\Test\StreamFilterTrait;

class VimaInstallTest extends VimaTestCase
{
    use StreamFilterTrait;

    protected $composerPath;
    protected $configPath;
    protected $autoloadPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->composerPath = ROOTPATH . 'composer.json';
        $this->configPath   = APPPATH . 'Config/Vima.php';
        $this->autoloadPath = APPPATH . 'Config/Autoload.php';

        // Backup existing composer.json
        if (file_exists($this->composerPath)) {
            copy($this->composerPath, $this->composerPath . '.bak');
        } else {
            file_put_contents($this->composerPath, json_encode(['require' => []]));
        }

        // Remove config if exists
        if (file_exists($this->configPath)) {
            rename($this->configPath, $this->configPath . '.bak');
        }

        // Backup Autoload.php
        if (file_exists($this->autoloadPath)) {
            copy($this->autoloadPath, $this->autoloadPath . '.bak');
        }
    }

    protected function tearDown(): void
    {
        // Restore backups
        if (file_exists($this->composerPath . '.bak')) {
            rename($this->composerPath . '.bak', $this->composerPath);
        }
        if (file_exists($this->configPath . '.bak')) {
            rename($this->configPath . '.bak', $this->configPath);
        }
        if (file_exists($this->autoloadPath . '.bak')) {
            rename($this->autoloadPath . '.bak', $this->autoloadPath);
        }
        parent::tearDown();
    }

    public function testInstallPublishesConfig()
    {
        command('vima:setup --overwrite');

        $this->assertFileExists($this->configPath);
        $content = file_get_contents($this->configPath);
        $this->assertStringContainsString('namespace Config;', $content);
        $this->assertStringContainsString('class Vima extends BaseVima', $content);
    }

    public function testRegisterHelperWithEmptyArray()
    {
        file_put_contents($this->autoloadPath, "<?php\nnamespace Config;\nclass Autoload {\n    public \$helpers = [];\n}\n");

        command('vima:setup --overwrite');

        $content = file_get_contents($this->autoloadPath);
        $this->assertStringContainsString("'Vima\\CodeIgniter\\Helpers\\vima'", $content);
        $this->assertStringNotContainsString(',,', $content);
    }

    public function testRegisterHelperWithExistingTrailingCommaDoesNotCreateDoubleComma()
    {
        file_put_contents($this->autoloadPath, "<?php\nnamespace Config;\nclass Autoload {\n    public \$helpers = ['Jengo\\Base\\Helpers\\jengo',];\n}\n");

        command('vima:setup --overwrite');

        $content = file_get_contents($this->autoloadPath);
        $this->assertStringContainsString("'Vima\\CodeIgniter\\Helpers\\vima'", $content);
        $this->assertStringContainsString("'Jengo\\Base\\Helpers\\jengo'", $content);
        $this->assertStringNotContainsString(',,', $content);
    }

    public function testRegisterHelperFixesExistingDoubleComma()
    {
        file_put_contents($this->autoloadPath, "<?php\nnamespace Config;\nclass Autoload {\n    public \$helpers = ['Jengo\\Base\\Helpers\\jengo',,\n        'Vima\\CodeIgniter\\Helpers\\vima'];\n}\n");

        command('vima:setup --overwrite');

        $content = file_get_contents($this->autoloadPath);
        $this->assertStringNotContainsString(',,', $content);
        $this->assertStringContainsString("'Vima\\CodeIgniter\\Helpers\\vima'", $content);
    }

    public function testRegisterHelperWithTypedProperty()
    {
        file_put_contents($this->autoloadPath, "<?php\nnamespace Config;\nclass Autoload {\n    public array \$helpers = [\n        'form',\n    ];\n}\n");

        command('vima:setup --overwrite');

        $content = file_get_contents($this->autoloadPath);
        $this->assertStringContainsString("'Vima\\CodeIgniter\\Helpers\\vima'", $content);
        $this->assertStringContainsString("'form'", $content);
        $this->assertStringNotContainsString(',,', $content);
    }
}
