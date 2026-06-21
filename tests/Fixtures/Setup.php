<?php

namespace Vima\CodeIgniter\Tests\Fixtures;

use Vima\Core\Config\Contracts\SetupProviderInterface;
use Vima\Core\Permission\Entities\Permission;
use Vima\Core\Role\Entities\Role;

final class Setup implements SetupProviderInterface
{
    public function get(): array
    {
        return [
            'roles' => [
                Role::define('admin', description: 'can access everything')->withPermissions(['*'])
            ],
            'permissions' => [
                Permission::define('test.view', description: 'This is a permssion')
            ]
        ];
    }
}