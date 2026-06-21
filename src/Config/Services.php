<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Vima\CodeIgniter\Config;

use CodeIgniter\Config\BaseService;
use Vima\CodeIgniter\Repositories\RoleParentRepository;
use Vima\CodeIgniter\Repositories\RoleRepository;
use Vima\CodeIgniter\Repositories\PermissionRepository;
use Vima\CodeIgniter\Repositories\RolePermissionRepository;
use Vima\CodeIgniter\Repositories\UserRoleRepository;
use Vima\CodeIgniter\Repositories\UserPermissionRepository;
use Vima\CodeIgniter\Repositories\UserDenyRepository;
use Vima\CodeIgniter\Repositories\UserRoleDenyRepository;
use Vima\CodeIgniter\Services\CacheAdapter;
use Vima\CodeIgniter\Support\VimaRegistrar;
use Vima\Core\AuthorizationService;
use Vima\Core\Cache\Contracts\CacheInterface;
use Vima\Core\Config\Services\SyncService;
use Vima\Core\Config\VimaConfig;
use Vima\Core\Events\Contracts\EventDispatcherInterface;
use Vima\CodeIgniter\Support\CodeIgniterEventDispatcher;
use Vima\Core\Permission\Contracts\PermissionRepositoryInterface;
use Vima\Core\Permission\Services\PermissionService;
use Vima\Core\Policy\Services\PolicyRegistry;
use Vima\Core\Role\Contracts\RoleParentRepositoryInterface;
use Vima\Core\Role\Contracts\RolePermissionRepositoryInterface;
use Vima\Core\Role\Contracts\RoleRepositoryInterface;
use Vima\Core\Role\Services\RoleService;
use Vima\Core\Support\Deployment\Services\DeploymentService;
use Vima\Core\Support\Mapping\MapGenerator;
use Vima\Core\Support\Mapping\MappingService;
use Vima\Core\User\Contracts\UserDenyRepositoryInterface;
use Vima\Core\User\Contracts\UserPermissionRepositoryInterface;
use Vima\Core\User\Contracts\UserRoleDenyRepositoryInterface;
use Vima\Core\User\Contracts\UserRoleRepositoryInterface;
use function Vima\Core\resolve;

if (!class_exists(Services::class, false)) {
    class Services extends BaseService
    {
        public static function vima_config(bool $getShared = true): VimaConfig
        {
            if ($getShared) {
                return static::getSharedInstance('vima_config');
            }

            /**
             * @var Vima
             */
            $ciConfig = config('Vima');

            return $ciConfig->toVimaConfig();
        }

        public static function vima_cache(bool $getShared = true): CacheInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_cache');
            }

            return new CacheAdapter(cache());
        }

        /**
         * Main Vima Access Manager
         */
        public static function vima(bool $getShared = true): AuthorizationService
        {
            if ($getShared) {
                VimaRegistrar::init(false);
                return static::getSharedInstance('vima');
            }

            VimaRegistrar::init(false);
            return resolve(AuthorizationService::class);
        }


        public static function vima_deployment(bool $getShared = true): DeploymentService
        {
            VimaRegistrar::init(false);
            if ($getShared) {
                return static::getSharedInstance('vima_deployment');
            }

            return new DeploymentService(
                resolve(RoleService::class),
                resolve(PolicyRegistry::class),
                service('vima_cache')
            );
        }

        public static function vima_events(bool $getShared = true): EventDispatcherInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_events');
            }
            return new CodeIgniterEventDispatcher();
        }

        public static function vima_roles(bool $getShared = true): RoleRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_roles');
            }
            return new RoleRepository();
        }

        public static function vima_permissions(bool $getShared = true): PermissionRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_permissions');
            }
            return new PermissionRepository();
        }

        public static function vima_role_permissions(bool $getShared = true): RolePermissionRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_role_permissions');
            }
            return new RolePermissionRepository();
        }

        public static function vima_user_roles(bool $getShared = true): UserRoleRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_user_roles');
            }
            return new UserRoleRepository();
        }

        public static function vima_user_permissions(bool $getShared = true): UserPermissionRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_user_permissions');
            }
            return new UserPermissionRepository();
        }

        public static function vima_user_denies(bool $getShared = true): UserDenyRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_user_denies');
            }
            return new UserDenyRepository();
        }

        public static function vima_user_role_denies(bool $getShared = true): UserRoleDenyRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_user_role_denies');
            }
            return new UserRoleDenyRepository();
        }

        public static function vima_role_parents(bool $getShared = true): RoleParentRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_role_parents');
            }
            return new RoleParentRepository();
        }

        /**
         * Request-scoped context storage
         */
        public static function vima_context(bool $getShared = true)
        {
            if ($getShared) {
                return static::getSharedInstance('vima_context');
            }

            return new ContextStorage();
        }

        public static function vima_sync(bool $getShared = true): SyncService
        {
            \Vima\CodeIgniter\Support\VimaRegistrar::init(false);
            if ($getShared) {
                return static::getSharedInstance('vima_sync');
            }

            return new SyncService(
                resolve(RoleService::class),
                resolve(PermissionService::class),
                service('vima_events'),
                service('vima_config')
            );
        }

        public static function vima_map_generator(bool $getShared = true): MapGenerator
        {
            if ($getShared) {
                return static::getSharedInstance('vima_map_generator');
            }

            $rootPath = defined('ROOTPATH') ? ROOTPATH : getcwd() . DIRECTORY_SEPARATOR;
            $mappingFile = $rootPath . '.vima' . DIRECTORY_SEPARATOR . 'mapping.json';

            return new MapGenerator(
                new MappingService($mappingFile),
                service('vima_events')
            );
        }
    }
}
