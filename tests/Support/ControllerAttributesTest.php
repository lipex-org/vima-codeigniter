<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Vima\CodeIgniter\Tests\Support;

use CodeIgniter\Config\Factories;
use CodeIgniter\Controller;
use CodeIgniter\Test\FeatureTestTrait;
use Vima\CodeIgniter\Filters\Attributes\VimaAuthorize;
use Vima\CodeIgniter\Filters\Attributes\VimaPolicy;
use Vima\CodeIgniter\Filters\Attributes\VimaResource;
use Vima\CodeIgniter\Tests\VimaTestCase;
use Vima\Core\Policy\Contracts\PolicyInterface;

// Define mock entities, model, and policies for the test
class AttributeResource
{
    public int $id = 42;
    public int $ownerId = 1;
}

class AttributeModel
{
    public function find($id)
    {
        $res = new AttributeResource();
        $res->id = (int) $id;
        return $res;
    }
}

class AttributePolicy implements PolicyInterface
{
    public static function getResource(): string
    {
        return AttributeResource::class;
    }

    public function canEdit($user, $resource)
    {
        return $resource->id === 42;
    }

    public function canDelete($user, $resource)
    {
        return false; // always deny delete
    }
}

// Define the controller using Vima attributes
class TestAttributeController extends Controller
{
    #[VimaAuthorize('users.manage')]
    public function manageUsers()
    {
        return 'Users Managed';
    }

    #[VimaPolicy(action: 'edit', resource: AttributeResource::class, idParam: 'id')]
    public function editResource($id)
    {
        return 'Resource Edited';
    }

    #[VimaResource(resource: AttributeResource::class)]
    public function delete($id)
    {
        return 'Resource Deleted';
    }
}

class ControllerAttributesTest extends VimaTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure Vima policies and user
        $config = config('Vima');
        $config->policies['registered'] = [AttributePolicy::class];
        $config->user['current'] = fn() => new \Vima\CodeIgniter\Tests\Fixtures\User(1);

        // Mock the model
        Factories::injectMock('models', AttributeResource::class, new AttributeModel());
        Factories::injectMock('config', 'Vima', $config);

        \Vima\CodeIgniter\Support\VimaRegistrar::init(true);
    }

    public function testVimaAuthorizeAttributeAllowsWhenPermitted()
    {
        // Define a policy rule that allows users.manage
        vima_policy('users.manage', function ($user) {
            return true;
        });

        $routes = service('routes');
        $routes->get('users/manage', [TestAttributeController::class, 'manageUsers']);

        $result = $this->get('users/manage');
        $result->assertStatus(200);
        $result->assertSee('Users Managed');
    }

    public function testVimaAuthorizeAttributeDeniesWhenForbidden()
    {
        // Define a policy rule that denies users.manage
        vima_policy('users.manage', function ($user) {
            return false;
        });

        $routes = service('routes');
        $routes->get('users/manage', [TestAttributeController::class, 'manageUsers']);

        $result = $this->get('users/manage');
        $result->assertStatus(403);
        $result->assertSee('Access Denied');
    }

    public function testVimaPolicyAttributeAllowsWhenPermitted()
    {
        $routes = service('routes');
        $routes->get('resource/edit/(:num)', [TestAttributeController::class, 'editResource']);

        // Resource with ID 42 is allowed by AttributePolicy
        $result = $this->get('resource/edit/42');
        $result->assertStatus(200);
        $result->assertSee('Resource Edited');
    }

    public function testVimaPolicyAttributeDeniesWhenForbidden()
    {
        $routes = service('routes');
        $routes->get('resource/edit/(:num)', [TestAttributeController::class, 'editResource']);

        // Resource with ID 99 is denied by AttributePolicy (ID must be 42)
        $result = $this->get('resource/edit/99');
        $result->assertStatus(403);
        $result->assertSee('Access Denied');
    }

    public function testVimaResourceAttributeDeniesAction()
    {
        $routes = service('routes');
        // Maps 'delete' controller action to 'delete' policy method, which always returns false
        $routes->delete('resource/delete/(:num)', [TestAttributeController::class, 'delete']);

        $result = $this->delete('resource/delete/42');
        $result->assertStatus(403);
        $result->assertSee('Access Denied');
    }
}
