<?php

namespace Vima\CodeIgniter\Tests\Support;

use Vima\CodeIgniter\Tests\VimaTestCase;
use Vima\CodeIgniter\Support\Discovery;
use Vima\Core\Policy\Contracts\PolicyRegistryInterface;
use Vima\Core\Policy\Contracts\PolicyInterface;
use function Vima\Core\resolve;

class DiscoveryTest extends VimaTestCase
{
    public function testDiscoverPolicies()
    {
        // Create a mock policy class in a temp location that CI4 can find
        // In CI4 tests, APPPATH usually points to tests/_support/App or similar
        // But for this test, we can just manually register a namespace

        $policyContent = <<<'PHP'
<?php
namespace Vima\CodeIgniter\Tests\Support\Fixtures;
use Vima\Core\Policy\Contracts\PolicyInterface;
class MockPolicy implements PolicyInterface {
    public static function getResource(): string { return 'MockResource'; }
}
PHP;
        $dir = __DIR__ . '/Fixtures';
        if (!is_dir($dir))
            mkdir($dir, 0777, true);
        file_put_contents($dir . '/MockPolicy.php', $policyContent);

        // Register the namespace so the locator can find it
        $autoloader = \Config\Services::autoloader();
        $autoloader->addNamespace('Vima\CodeIgniter\Tests\Support', dirname($dir));

        // Run discovery
        Discovery::discoverPolicies('Fixtures');

        /** @var PolicyRegistryInterface $registry */
        $registry = resolve(PolicyRegistryInterface::class);
        $classes = $registry->getRegisteredClasses();

        $this->assertArrayHasKey('MockResource', $classes);
        $this->assertEquals('Vima\CodeIgniter\Tests\Support\Fixtures\MockPolicy', $classes['MockResource']);

        // Cleanup
        unlink($dir . '/MockPolicy.php');
        rmdir($dir);
    }
}
