<?php

namespace Vima\CodeIgniter\Tests\Filters;

use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Config\Factories;
use Config\Services;

class ResourceFilterMockResource
{
    public $id;
    public function __construct($id)
    {
        $this->id = $id;
    }
}

class ResourceFilterMockModel
{
    public function find($id)
    {
        return new ResourceFilterMockResource($id);
    }

    public function customFind($id)
    {
        return new ResourceFilterMockResource("custom-" . $id);
    }
}

class VimaResourceFilterTest extends VimaTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        Factories::injectMock('models', 'ResourceFilterMockModel', new ResourceFilterMockModel());
        Services::vima_context()->set(null); // Clear context
    }

    public function testResourceFilterSetsContext()
    {
        $routes = service('routes');
        $routes->get('posts/(:num)', function ($id) {
            $resource = vima_context();
            return $resource ? "Found " . $resource->id : "Not Found";
        }, ['filter' => 'vima_resource:ResourceFilterMockModel,2']);

        $result = $this->get('posts/123');
        $result->assertStatus(200);
        $result->assertSee('Found 123');
    }

    public function testResourceFilterWithCustomResolverMethod()
    {
        $routes = service('routes');
        $routes->get('custom/(:num)', function ($id) {
            $resource = vima_context();
            return $resource ? "Found " . $resource->id : "Not Found";
        }, ['filter' => 'vima_resource:ResourceFilterMockModel,2,customFind']);

        $result = $this->get('custom/123');
        $result->assertStatus(200);
        $result->assertSee('Found custom-123');
    }
}
