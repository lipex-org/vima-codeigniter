<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Commands\Actions\User;

use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Commands\Actions\VimaActionInterface;
use Vima\CodeIgniter\Commands\Actions\BaseAction;
use Vima\CodeIgniter\Support\Utils;

class PermitAction extends BaseAction
{
    public function execute(array $params): void
    {
        $user_id = $params[0] ?? CLI::prompt('User ID', null, 'required');
        $permission = $params[1] ?? CLI::prompt('Permission', null, 'required');

        if (empty($user_id) || empty($permission)) {
            CLI::write('User and permission are required.', 'red');
            return;
        }

        $user = Utils::creatVimaUser($user_id);

        try {
            $permissionEntity = \Vima\Core\Vima::permissions()->find($permission);

            if (empty($permissionEntity)) {
                CLI::write('Permission not found.', 'red');
                return;
            }

            \Vima\Core\Vima::user($user)->grant()->permission($permissionEntity);

            CLI::write('User permitted to perform permission.', 'green');
        } catch (\Exception $e) {
            CLI::write($e->getMessage(), 'red');
        }
    }

    public function getDescription(): string
    {
        return 'Permit a user to perform a permission';
    }

    public function getUsage(): string
    {
        return 'vima user permit [user_id] [permission_name]';
    }
}
