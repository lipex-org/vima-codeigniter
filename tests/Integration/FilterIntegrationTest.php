<?php

namespace Vima\CodeIgniter\Tests\Integration;

use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Vima\Core\Policy\Contracts\PolicyInterface;
use CodeIgniter\Config\Factories;

class IntegrationResource
{
    public int $ownerId = 1;
}

class IntegrationModel
{
    public function find($id)
    {
        return new IntegrationResource();
    }
}

class IntegrationPolicy implements PolicyInterface
{
    public static function getResource(): string
    {
        return IntegrationResource::class;
    }
    public function canEdit($user, $resource)
    {
        return true;
    }
}

class FailingPolicy implements PolicyInterface
{
    public static function getResource(): string
    {
        return IntegrationResource::class;
    }
    public function canEdit($user, $resource)
    {
        return false;
    }
}

class FilterIntegrationTest extends VimaTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Vima Config
        $config = config('Vima');
        $config->policies['registered'] = [IntegrationPolicy::class];
        $config->user['current'] = fn() => new \Vima\CodeIgniter\Tests\Fixtures\User(1);

        // Inject mock model
        Factories::injectMock('models', 'IntegrationModel', new IntegrationModel());
        Factories::injectMock('config', 'Vima', $config);

        \Vima\CodeIgniter\Support\VimaRegistrar::init(true);
    }

    public function testFullFilterLifecycle()
    {
        // Define a mock route with the filters
        $routes = service('routes');
        $routes->get('test-lifecycle', function () {
            return 'OK';
        }, ['filter' => ['vima_resource:IntegrationModel', 'vima_authorize:edit']]);

        $result = $this->get('test-lifecycle');

        $result->assertStatus(200);
        $result->assertSee('OK');
    }

    public function testFilterLifecycleFailsWhenUnauthorized()
    {
        $config = config('Vima');
        $config->policies['registered'] = [FailingPolicy::class];
        \CodeIgniter\Config\Factories::injectMock('config', 'Vima', $config);
        \Vima\CodeIgniter\Support\VimaRegistrar::init(true);

        $routes = service('routes');
        $routes->get('test-failing', function () {
            return 'OK';
        }, ['filter' => ['vima_resource:IntegrationModel', 'vima_authorize:edit']]);

        $result = $this->get('test-failing');
        $result->assertStatus(403);
        $result->assertSee('Access Denied');
    }
}
