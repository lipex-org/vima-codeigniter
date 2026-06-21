<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


use Config\Services;
use Vima\CodeIgniter\Config\Vima;
use Vima\Core\AuthorizationService;

if (!function_exists('vima')) {
    /**
     * Returns the Vima Access Manager service.
     */
    function vima(): AuthorizationService
    {
        return Services::vima();
    }
}

if (!function_exists('can')) {
    /**
     * Check if the current user has the given permission.
     * 
     * @param string $permission The permission to check.
     * @param mixed ...$arguments The arguments to pass to the policy callback. You can pass a namespace as the first argument.
     * @return bool
     */
    function can(string $permission, ...$arguments): bool
    {
        $user = null;
        /**
         * @var Vima
         */
        $config = config('Vima');

        if ($config) {
            $user = $config->getCurrentUser();
        } else {
            try {
                if (function_exists('auth')) {
                    $user = auth()->user();
                } else {
                    $user = service('auth')->user();
                }
            } catch (\Throwable $e) {
                $user = null;
            }
        }

        if (!$user) {
            throw new \Exception("Vima could not resolve the current user. Please ensure a user is logged in or define 'currentUser' in your Vima configuration. This exception might also be triggered when you use 'can' helper function when not logged in");
        }

        if (str_contains($permission, ':')) {
            // Already contains namespace
        } elseif (!empty($arguments) && is_string($arguments[0])) {
            $namespace = array_shift($arguments);
            $permission = "{$namespace}:{$permission}";
        }

        return vima()->can($user, $permission, ...$arguments);
    }
}


if (!function_exists("can_any")) {
    /**
     * Performs authorization checks on each permssion given and returns true on the first permitted action
     * @param array $permissions
     * @param array $arguments
     * @return bool
     */
    function can_any(array $permissions, ...$arguments): bool
    {
        foreach ($permissions as $perm) {
            if (can($perm, ...$arguments)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists("can_all")) {
    /**
     * Performs authorization checks on each permssion given and returns false on the first non-permitted action
     * @param array $permissions
     * @param array $arguments
     * @return bool
     */
    function can_all(array $permissions, ...$arguments): bool
    {
        foreach ($permissions as $perm) {
            if (!can($perm, ...$arguments)) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('vima_context')) {
    /**
     * Get or set request-scoped context object.
     */
    function vima_context(?object $context = null)
    {
        $storage = Services::vima_context();
        if ($context !== null) {
            $storage->set($context);
        }
        return $storage->get();
    }
}

if (!function_exists('vima_policy')) {
    /**
     * Define a new policy.
     */
    function vima_policy(string $action, callable $callback): void
    {
        $registry = \Vima\Core\resolve(\Vima\Core\Policy\Contracts\PolicyRegistryInterface::class);
        $registry->register($action, $callback);
    }
}
