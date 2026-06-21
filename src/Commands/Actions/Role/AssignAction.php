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
use Vima\CodeIgniter\Support\Utils;
use Vima\Core\Vima;

class AssignAction extends BaseAction
{
    public function execute(array $params): void
    {
        $userId = $params[0] ?? CLI::prompt('User ID');
        $roleName = $params[1] ?? CLI::prompt('Role Name');
        $namespace = CLI::getOption('namespace');

        try {
            if (!str_contains($roleName, ':') && $namespace) {
                $roleName = "$namespace:$roleName";
            }

            $user = Utils::creatVimaUser($userId);
            Vima::user($user)->grant()->role($roleName);

            $msg = "Role [{$roleName}]";
            if ($namespace) {
                $msg .= " in namespace [{$namespace}]";
            }
            $msg .= " assigned to user [{$userId}] successfully.";

            CLI::write($msg, 'green');
        } catch (\Throwable $e) {
            CLI::error("Failed to assign role: " . $e->getMessage());
        }
    }

    public function getDescription(): string
    {
        return 'Assign a role to a user';
    }

    public function getUsage(): string
    {
        return 'vima role assign [user_id] [role_name] [options]';
    }
}
