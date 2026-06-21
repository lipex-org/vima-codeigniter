<?php

namespace Vima\CodeIgniter\Tests\Filters;

use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Vima\Core\Role\Entities\Role;
use Vima\Core\User\Services\UserService;
use function Vima\Core\resolve;

class VimaRbacFilterTest extends VimaTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $config = config('Vima');
        $config->user['current'] = fn() => new \Vima\CodeIgniter\Tests\Fixtures\User(1);
        \Vima\CodeIgniter\Support\VimaRegistrar::init(true);
    }

    public function testRbacFilterAllowsWhenUserHasRole()
    {
        $user = config('Vima')->getCurrentUser();
        
        // Register the role in Vima
        resolve(\Vima\Core\Role\Services\RoleService::class)->save(new Role('admin'));
        // Grant role to the user
        resolve(UserService::class)->user($user)->grant()->role('admin');

        $routes = service('routes');
        $routes->get('test-rbac-ok', function () {
            return 'OK';
        }, ['filter' => 'vima_rbac:admin']);

        $result = $this->get('test-rbac-ok');
        $result->assertStatus(200);
        $result->assertSee('OK');
    }

    public function testRbacFilterBlocksWhenUserLacksRole()
    {
        $routes = service('routes');
        $routes->get('test-rbac-fail', function () {
            return 'OK';
        }, ['filter' => 'vima_rbac:admin']);

        $result = $this->get('test-rbac-fail');
        $result->assertStatus(403);
    }

    public function testRbacFilterRedirectsWhenPageProvided()
    {
        $routes = service('routes');
        $routes->get('test-rbac-redirect', function () {
            return 'OK';
        }, ['filter' => 'vima_rbac:admin,login']);
        $routes->get('login', function () {
            return 'Login Page';
        });

        $result = $this->get('test-rbac-redirect');
        $result->assertRedirectTo('login');
    }
}
