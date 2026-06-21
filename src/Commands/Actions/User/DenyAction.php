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

class DenyAction extends BaseAction
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

        $reason = $params[2] ?? null;
        if ($reason === null) {
            $reason = CLI::prompt('Reason (Optional)', '');
        }

        $isRole = $this->getOption('role', $params) !== null;
        $duration = $this->getOption('for', $params);

        $expiresAt = null;
        if ($duration) {
            try {
                $expiresAt = new \DateTime('+' . $duration);
            } catch (\Exception $e) {
                CLI::error("Invalid duration format: {$duration}");
                return;
            }
        }

        if (empty($user_id) || empty($target)) {
            CLI::error('User ID and Target name are required.');
            return;
        }

        $user = Utils::creatVimaUser($user_id);

        try {
            if ($isRole) {
                \Vima\Core\Vima::user($user)->deny()->role($target, $reason, $expiresAt);
                $msg = "Role [{$target}] explicitly denied to user [{$user_id}]";
            } else {
                \Vima\Core\Vima::user($user)->deny()->permission($target, $reason, $expiresAt);
                $msg = "Permission [{$target}] explicitly denied to user [{$user_id}]";
            }

            if ($reason)
                $msg .= " with reason: {$reason}";
            if ($expiresAt)
                $msg .= " until " . $expiresAt->format('Y-m-d H:i:s');

            CLI::write($msg, 'green');
        } catch (\Exception $e) {
            CLI::error($e->getMessage());
            throw $e;
        }
    }

    public function getDescription(): string
    {
        return 'Explicitly deny a permission or role to a user';
    }

    public function getUsage(): string
    {
        return 'vima user deny [user_id] [permission_or_role] [reason] [options]';
    }
}
