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

class UndenyAction extends BaseAction
{
    public function execute(array $params): void
    {
        $user_id = $params[0] ?? null;
        if (empty($user_id)) {
            $user_id = CLI::prompt('User ID', null, 'required');
        }

        $target = $params[1] ?? null;
        if (empty($target)) {
            $target = CLI::prompt('Permission/Role Name', null, 'required');
        }

        $isRole = isset($params['role']) || CLI::getOption('role') !== null;

        if (empty($user_id) || empty($target)) {
            CLI::error('User ID and target name are required.');
            return;
        }

        $user = Utils::creatVimaUser($user_id);

        try {
            if ($isRole) {
                \Vima\Core\Vima::user($user)->undeny()->role($target);
                CLI::write("Role [{$target}] denial removed for user [{$user_id}].", 'green');
            } else {
                \Vima\Core\Vima::user($user)->undeny()->permission($target);
                CLI::write("Permission [{$target}] denial removed for user [{$user_id}].", 'green');
            }
        } catch (\Exception $e) {
            CLI::error($e->getMessage());
        }
    }

    public function getDescription(): string
    {
        return 'Remove an explicit denial from a user';
    }

    public function getUsage(): string
    {
        return 'vima user undeny [user_id] [permission_or_role] [options]';
    }
}
