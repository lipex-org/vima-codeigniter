<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Traits;

use Vima\CodeIgniter\Config\Vima;
use Vima\Core\Exceptions\AccessDeniedExceptionInterface;
use Vima\Core\Exceptions\AccessDeniedException;
use Vima\Core\Vima as VimaCore;

/**
 * Trait VimaTrait
 * 
 * Provides convenient authorization methods for CodeIgniter 4 controllers.
 */
trait VimaTrait
{
    /**
     * Authorize the current user for the given permission.
     *
     * @param mixed $permission Or resource object
     * @param mixed ...$arguments
     * @throws AccessDeniedExceptionInterface
     */
    protected function authorize(string $permission, ...$arguments): void
    {
        if (!$this->can((string) $permission, ...$arguments)) {
            throw AccessDeniedException::forPermission((string) $permission);
        }
    }

    /**
     * Authorizes any of the permssions provided. Throws an exception if all are forbidden
     * @param array $permissions
     * @param array $arguments
     * @throws AccessDeniedExceptionInterface
     * @return void
     */
    protected function authorize_any(array $permissions, ...$arguments): void
    {
        if (!$this->can_any($permissions, ...$arguments)) {
            throw AccessDeniedException::forPermission(implode(' or ', $permissions));
        }
    }


    /**
     * Authorizes all of the permssions provided. Throws an exception if any is forbidden
     * @param array $permissions
     * @param array $arguments
     * @throws AccessDeniedExceptionInterface
     * @return void
     */
    protected function authorize_all(array $permissions, ...$arguments): void
    {
        if (!$this->can_all($permissions, ...$arguments)) {
            throw AccessDeniedException::forPermission(implode(' or ', $permissions));
        }
    }

    /**
     * Check if the current user has the given permission.
     *
     * @param string $permission
     * @param mixed ...$arguments
     * @return bool
     */
    protected function can(string $permission, ...$arguments): bool
    {
        return service('vima')->can($this->currentVimaUser(), $permission, ...$arguments);
    }

    /**
     * Performs a can check on all of the permissions provided and returns true on the first permissible action for the current user
     * @param array $permissions
     * @param array $arguments
     * @return bool
     */
    protected function can_any(array $permissions, ...$arguments): bool
    {
        return can_any($permissions, ...$arguments);
    }

    /**
     * Performs a can check on all of the permissions provided and returns true on the first impermissible action for the current user
     * @param array $permissions
     * @param array $arguments
     * @return bool
     */
    protected function can_all(array $permissions, ...$arguments): bool
    {
        return can_all($permissions, ...$arguments);
    }

    /**
     * Get the currently authenticated user based on configuration.
     *
     * @return object|null
     * @throws \RuntimeException
     */
    protected function currentVimaUser(): ?object
    {
        /**
         * @var Vima $config
         */
        $config = config('Vima');

        if ($config && isset($config->user['current'])) {
            return $config->getCurrentUser();
        }

        try {
            if (function_exists('auth')) {
                return auth()->user();
            } else {
                return service('auth')->user();
            }
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Explicitly deny a permission to the current user.
     *
     * @param string $permission
     * @param string|null $reason
     * @return void
     */
    protected function denyUser(string $permission, ?string $reason = null): void
    {
        $user = $this->currentVimaUser() ?? throw new \RuntimeException('No authenticated user found for denyUser().');
        VimaCore::user($user)->deny()->permission($permission, $reason);
    }

    /**
     * Check if the current user is explicitly denied a permission.
     *
     * @param string $permission
     * @return bool
     */
    protected function isDenied(string $permission): bool
    {
        $user = $this->currentVimaUser();
        if (!$user) {
            return false;
        }
        return VimaCore::user($user)->is()->denied()->permission($permission);
    }

    /**
     * Explicitly deny a role to the current user.
     *
     * @param string $role
     * @param string|null $reason
     * @param \DateTimeInterface|null $expiresAt
     * @return void
     */
    protected function denyRole(string $role, ?string $reason = null, ?\DateTimeInterface $expiresAt = null): void
    {
        $user = $this->currentVimaUser() ?? throw new \RuntimeException('No authenticated user found for denyRole().');
        VimaCore::user($user)->deny()->role($role, $reason, $expiresAt);
    }

    /**
     * Remove an explicit role denial for the current user.
     *
     * @param string $role
     * @return void
     */
    protected function undenyRole(string $role): void
    {
        $user = $this->currentVimaUser() ?? throw new \RuntimeException('No authenticated user found for undenyRole().');
        VimaCore::user($user)->undeny()->role($role);
    }

    /**
     * Check if the current user is explicitly denied a role.
     *
     * @param string $role
     * @return bool
     */
    protected function isRoleDenied(string $role): bool
    {
        $user = $this->currentVimaUser();
        if (!$user) {
            return false;
        }
        return VimaCore::user($user)->is()->denied()->role($role);
    }
}
