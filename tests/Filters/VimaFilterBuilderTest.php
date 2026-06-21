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

namespace Vima\CodeIgniter\Tests\Filters;

use PHPUnit\Framework\TestCase;
use Vima\CodeIgniter\Filters\VimaFilterBuilder;

class VimaFilterBuilderTest extends TestCase
{
    public function testAuthorizeBuilder()
    {
        $this->assertSame('vima_authorize:posts.edit', VimaFilterBuilder::authorize('posts.edit'));
        $this->assertSame('vima_authorize:posts.edit,/error-page', VimaFilterBuilder::authorize('posts.edit', '/error-page'));
    }

    public function testRbacBuilder()
    {
        $this->assertSame('vima_rbac:admin', VimaFilterBuilder::rbac('admin'));
        $this->assertSame('vima_rbac:admin,/error-page', VimaFilterBuilder::rbac('admin', '/error-page'));
    }

    public function testResourceBuilder()
    {
        $this->assertSame('vima_resource:PostModel,1', VimaFilterBuilder::resource('PostModel'));
        $this->assertSame('vima_resource:PostModel,2', VimaFilterBuilder::resource('PostModel', 2));
        $this->assertSame('vima_resource:PostModel,2,customFind', VimaFilterBuilder::resource('PostModel', 2, 'customFind'));
    }

    public function testPolicyBuilder()
    {
        $this->assertSame(
            'vima_policy:posts.edit:App\Policies\PostPolicy::canEdit',
            VimaFilterBuilder::policy('posts.edit', 'App\Policies\PostPolicy::canEdit')
        );
    }
}
