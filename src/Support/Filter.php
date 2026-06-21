<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Support;

/**
 * Helper class to generate Vima filter strings for routes.
 */
class Filter
{
    /**
     * Generate an authorization filter string.
     * 
     * @param string $permission
     * @return string
     */
    public static function can(string $permission, string $page = '/'): string
    {
        return "vima_authorize:{$permission},{$page}";
    }

    /**
     * Generate a resource loading filter string.
     * 
     * @param string $modelName The model to use for loading (e.g. PostModel)
     * @param int $segment The URI segment containing the ID
     * @param string|null $segmentResolver Optional function name or model method to resolve the ID
     * @return string
     */
    public static function resource(string $modelName, int $segment = 1, ?string $segmentResolver = null): string
    {
        $arg = "{$modelName},{$segment}";
        if ($segmentResolver) {
            $arg .= ",{$segmentResolver}";
        }
        return "vima_resource:{$arg}";
    }

    /**
     * Generate a policy registration filter string.
     * 
     * @param string $action The action name
     * @param string $callback The callback string (e.g. App\Policies\PostPolicy::canEdit)
     * @return string
     */
    public static function policy(string $action, string $callback): string
    {
        return "vima_policy:{$action}:{$callback}";
    }

    /**
     * Generate a setup filter string.
     * 
     * @return string
     */
    public static function setup(): string
    {
        return "vima_setup";
    }
}
