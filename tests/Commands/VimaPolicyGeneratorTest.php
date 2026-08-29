<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\Test\StreamFilterTrait;

class VimaPolicyGeneratorTest extends VimaTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure Policies directory exists in app
        if (!is_dir(APPPATH . 'Policies')) {
            mkdir(APPPATH . 'Policies', 0777, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up generated files
        if (file_exists(APPPATH . 'Policies/BlogPolicy.php')) {
            unlink(APPPATH . 'Policies/BlogPolicy.php');
        }
        $customPolicyPath = dirname(__DIR__, 2) . '/src/Policies/CustomTestPolicy.php';
        if (file_exists($customPolicyPath)) {
            unlink($customPolicyPath);
        }
        parent::tearDown();
    }

    public function testGeneratorCreatesFileWithCorrectContent()
    {
        command('vima:policy create BlogPolicy --resource "App\\\\Entities\\\\Blog"');

        $this->assertFileExists(APPPATH . 'Policies/BlogPolicy.php');

        $content = file_get_contents(APPPATH . 'Policies/BlogPolicy.php');
        $this->assertStringContainsString('class BlogPolicy implements PolicyInterface', $content);
        $this->assertStringContainsString('use App\Entities\Blog;', $content);
        $this->assertStringContainsString('return Blog::class;', $content);
        $this->assertStringContainsString('public function canView(AccessContext $ctx, Blog $blog): bool', $content);
        $this->assertStringContainsString('use Vima\Core\Policy\DTOs\AccessContext;', $content);
        $this->assertStringContainsString('use Vima\Core\Policy\Attributes\MapToPermission;', $content);
        $this->assertStringContainsString('#[MapToPermission(\'edit\')]', $content);
        $this->assertStringContainsString('public function customEditMethod(AccessContext $ctx, Blog $blog): bool', $content);
    }

    public function testGeneratorOverwritesWithForce()
    {
        file_put_contents(APPPATH . 'Policies/BlogPolicy.php', 'Original Content');

        command('vima:policy create BlogPolicy --resource "App\\\\Entities\\\\Blog" --force');

        $content = file_get_contents(APPPATH . 'Policies/BlogPolicy.php');
        $this->assertStringNotContainsString('Original Content', $content);
        $this->assertStringContainsString('class BlogPolicy', $content);
    }

    public function testGeneratorCreatesFileWithCustomNamespace()
    {
        command('vima:policy create CustomTestPolicy --namespace "Vima\\\\CodeIgniter" --resource "App\\\\Entities\\\\Blog"');

        $customPolicyPath = dirname(__DIR__, 2) . '/src/Policies/CustomTestPolicy.php';
        $this->assertFileExists($customPolicyPath);

        $content = file_get_contents($customPolicyPath);
        $this->assertStringContainsString('namespace Vima\CodeIgniter\Policies;', $content);
        $this->assertStringContainsString('class CustomTestPolicy implements PolicyInterface', $content);
    }

    public function testPolicyListAction()
    {
        // First create a policy to ensure there is at least one auto-discovered policy
        command('vima:policy create BlogPolicy --resource "App\\\\Entities\\\\Blog"');

        command('vima:policy list');

        $output = $this->getStreamFilterBuffer();

        $this->assertStringContainsString('BlogPolicy', $output);
        $this->assertStringContainsString('App\Entities\Blog', $output);
    }

    public function testPolicyCacheAction()
    {
        command('vima:policy create BlogPolicy --resource "App\\\\Entities\\\\Blog"');

        command('vima:policy cache');

        $output = $this->getStreamFilterBuffer();

        $this->assertStringContainsString('BlogPolicy', $output);
        $this->assertStringContainsString('Successfully cached', $output);
    }

    public function testPolicyCacheClearAction()
    {
        command('vima:policy cache --clear');

        $output = $this->getStreamFilterBuffer();

        $this->assertStringContainsString('Policy mappings cache cleared successfully', $output);
    }
}
