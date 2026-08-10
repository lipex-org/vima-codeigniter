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

use Vima\CodeIgniter\Repositories\AuditRepository;
use Vima\Core\Audit\Contracts\AuditRepositoryInterface;
use Vima\Core\AuthorizationService;
use Vima\Core\Cache\Contracts\CacheInterface;
use Vima\Core\Config\VimaConfig;
use Vima\Core\Events\Contracts\EventDispatcherInterface;
use Vima\Core\Permission\Contracts\PermissionRepositoryInterface;
use Vima\Core\Permission\Services\PermissionService;
use Vima\Core\Policy\Contracts\PolicyRegistryInterface;
use Vima\Core\Policy\Services\PolicyRegistry;
use Vima\Core\Role\Contracts\RoleParentRepositoryInterface;
use Vima\Core\Role\Contracts\RolePermissionRepositoryInterface;
use Vima\Core\Role\Contracts\RoleRepositoryInterface;
use Vima\Core\Role\Services\RoleService;
use Vima\Core\Support\Deployment\Services\DeploymentService;
use Vima\Core\Support\Discovery\Container;
use Vima\Core\Support\Discovery\CoreBootstrapper;
use Vima\Core\User\Contracts\UserDenyRepositoryInterface;
use Vima\Core\User\Contracts\UserPermissionRepositoryInterface;
use Vima\Core\User\Contracts\UserRoleDenyRepositoryInterface;
use Vima\Core\User\Contracts\UserRoleRepositoryInterface;


/**
 * Class VimaRegistrar
 * 
 * Handles the registration of Vima Core dependencies with CodeIgniter 4 implementations.
 */
class VimaRegistrar
{
    private static bool $initialized = false;

    /**
     * Initializes the Vima Core Dependency Container.
     * 
     * @param bool $force Force re-initialization.
     * @return void
     */
    public static function init(bool $force = false): void
    {
        if (self::$initialized && !$force) {
            return;
        }

        if ($force) {
            Container::reset();
            PolicyRegistry::reset();
            self::$initialized = false;
        }

        $container = Container::getInstance();

        // 1. Run bootstrap first so default bindings are registered
        CoreBootstrapper::bootstrap($container);

        // 2. Overwrite defaults with CI4-specific implementations
        $container->register(EventDispatcherInterface::class, fn() => service('vima_events'));
        $container->register(CacheInterface::class, fn() => service('vima_cache'));

        // Register repositories
        $container->register(RoleRepositoryInterface::class, fn() => service('vima_roles'));
        $container->register(PermissionRepositoryInterface::class, fn() => service('vima_permissions'));
        $container->register(RolePermissionRepositoryInterface::class, fn() => service('vima_role_permissions'));
        $container->register(UserRoleRepositoryInterface::class, fn() => service('vima_user_roles'));
        $container->register(UserPermissionRepositoryInterface::class, fn() => service('vima_user_permissions'));
        $container->register(RoleParentRepositoryInterface::class, fn() => service('vima_role_parents'));
        $container->register(UserDenyRepositoryInterface::class, fn() => service('vima_user_denies'));
        $container->register(UserRoleDenyRepositoryInterface::class, fn() => service('vima_user_role_denies'));
        $container->register(AuditRepositoryInterface::class, fn() => new AuditRepository());

        // Register Config and User Resolver
        $container->register(VimaConfig::class, fn() => service('vima_config', false));
        // Inject mock config if Config\Vima mock is injected
        \CodeIgniter\Config\Factories::injectMock('config', 'Vima', config('Vima'));

        $container->register(PolicyRegistryInterface::class, fn() => PolicyRegistry::instance());

        // Register framework specific AccessDeniedException implementation
        \Vima\Core\Exceptions\AccessDeniedException::useFactory(
            fn(string $permission, mixed $user = null, mixed $userResolver = null) =>
                \Vima\CodeIgniter\Exceptions\AccessDeniedException::forPermission($permission, $user, $userResolver)
        );

        if (class_exists(\Config\Services::class)) {
            \Config\Services::injectMock('vima', $container->get(AuthorizationService::class));
        }

        Discovery::discoverPolicies();

        self::$initialized = true;
    }
}
