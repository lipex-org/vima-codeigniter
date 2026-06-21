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

namespace Vima\CodeIgniter\Filters;

/**
 * Helper to build CodeIgniter 4 filter string configurations in a type-safe manner.
 * Minimizes manual string formatting and provides guidance through method signatures.
 */
class VimaFilterBuilder
{
    /**
     * Build the 'vima_authorize' filter configuration string.
     *
     * Example: VimaFilterBuilder::authorize('posts.edit') => 'vima_authorize:posts.edit'
     *
     * @param string $permission The permission to enforce (e.g. 'posts.edit')
     * @param string|null $redirectPage Optional page URI/route to redirect to if authorization fails (instead of rendering 403)
     * @return string
     */
    public static function authorize(string $permission, ?string $redirectPage = null): string
    {
        $filter = 'vima_authorize:' . $permission;

        if ($redirectPage !== null) {
            $filter .= ',' . $redirectPage;
        }

        return $filter;
    }

    /**
     * Build the 'vima_rbac' filter configuration string.
     *
     * Example: VimaFilterBuilder::rbac('admin') => 'vima_rbac:admin'
     *
     * @param string $role The role to enforce (e.g. 'admin')
     * @param string|null $redirectPage Optional page URI/route to redirect to if authorization fails (instead of rendering 403)
     * @return string
     */
    public static function rbac(string $role, ?string $redirectPage = null): string
    {
        $filter = 'vima_rbac:' . $role;

        if ($redirectPage !== null) {
            $filter .= ',' . $redirectPage;
        }

        return $filter;
    }

    /**
     * Build the 'vima_resource' filter configuration string.
     *
     * Example: VimaFilterBuilder::resource('PostModel', 1) => 'vima_resource:PostModel,1'
     *
     * @param string $modelName Name of the CodeIgniter model or service to load (e.g. 'PostModel')
     * @param int $segmentIndex The URL route segment index (1-indexed) containing the resource ID
     * @param string|null $resolver Optional custom method name on the model to resolve the resource
     * @return string
     */
    public static function resource(string $modelName, int $segmentIndex = 1, ?string $resolver = null): string
    {
        $filter = 'vima_resource:' . $modelName . ',' . $segmentIndex;

        if ($resolver !== null) {
            $filter .= ',' . $resolver;
        }

        return $filter;
    }

    /**
     * Build the 'vima_policy' filter configuration string.
     *
     * Example: VimaFilterBuilder::policy('posts.edit', 'App\Policies\PostPolicy::canEdit')
     *
     * @param string $action The ability/action name (e.g. 'posts.edit')
     * @param string $policyMethod The static callback definition in format 'ClassName::method'
     * @return string
     */
    public static function policy(string $action, string $policyMethod): string
    {
        return 'vima_policy:' . $action . ':' . $policyMethod;
    }
}
