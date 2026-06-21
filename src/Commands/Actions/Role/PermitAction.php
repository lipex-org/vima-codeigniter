<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Commands\Actions\Role;

use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Commands\Actions\BaseAction;
use Vima\Core\Vima;

class PermitAction extends BaseAction
{
    public function execute(array $params): void
    {
        $role = $params[0] ?? CLI::prompt('Role name', null, 'required');
        $permission = $params[1] ?? CLI::prompt('Permission name', null, 'required');

        if (empty($role) || empty($permission)) {
            CLI::write('Role and permission are required.', 'red');
            return;
        }

        try {
            $roleRes = Vima::role($role);
            if (!$roleRes->exists()) {
                CLI::write("Role '{$role}' not found.", 'red');
                return;
            }

            $permRes = Vima::permission($permission);
            if (!$permRes->exists()) {
                CLI::write("Permission '{$permission}' not found.", 'red');
                return;
            }

            $roleRes->permissions()->add($permission);
            CLI::write('Role permitted to perform permission.', 'green');
        } catch (\Exception $e) {
            CLI::write($e->getMessage(), 'red');
        }
    }

    public function getDescription(): string
    {
        return 'Permit a role to perform a permission';
    }

    public function getUsage(): string
    {
        return 'vima role permit [role_name] [permission_name]';
    }
}
