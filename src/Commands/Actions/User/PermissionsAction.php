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

class PermissionsAction extends BaseAction
{
    public function execute(array $params): void
    {
        $user_id = $params[0] ?? CLI::prompt('User ID', null, 'required');
        $limit = (int) (isset($params['limit']) ? $params['limit'] : (CLI::getOption('limit') ?? 30));

        if (empty($user_id)) {
            CLI::write('User ID is required.', 'red');
            return;
        }

        $user = Utils::creatVimaUser($user_id);

        $userIdResolved = \Vima\Core\Vima::user($user)->getId();
        CLI::write('Shwoing permissions for user: ' . $userIdResolved, 'green');

        try {
            $permissions = \Vima\Core\Vima::user($user)->get()->permissions()->direct();

            $body = [];
            foreach ($permissions as $perm) {
                $body[] = [
                    $perm->id,
                    $perm->namespace ?? '[--GLOBAL--]',
                    $perm->name,
                    Utils::truncate($perm->description, $limit),
                ];
            }

            CLI::table($body, ['ID', 'Namespace', 'Name', 'Description']);
        } catch (\Exception $e) {
            CLI::write($e->getMessage(), 'red');
        }
    }

    public function getDescription(): string
    {
        return 'List all user permissions';
    }

    public function getUsage(): string
    {
        return 'vima user permissions [user_id]';
    }
}
