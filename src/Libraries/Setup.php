<?php

declare(strict_types=1);

namespace Vima\CodeIgniter\Libraries;

use Vima\Core\Config\Contracts\SetupProviderInterface;
use Vima\Core\Permission\Entities\Permission;
use Vima\Core\Role\Entities\Role;

final class Setup implements SetupProviderInterface
{
    public function get(): array
    {
        return [
            'roles' => [
                /**
                 * Insert your roles here
                 * 
                 * Role::define(
                 *     name: 'admin',
                 *     description: 'Admin role',
                 *     permissions: [
                 *         'post.create',
                 *         'post.update',
                 *         'post.delete',
                 *     ]
                 * );
                 */
            ],
            'permissions' => [
                /**
                 * Insert your permissions here
                 * 
                 * Permission::define(
                 *     name: 'post.create',
                 *     description: 'Create post',
                 * );
                 */
            ]
        ];
    }
}
