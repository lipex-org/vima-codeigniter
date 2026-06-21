<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Commands\Actions\Permission;

use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Commands\Actions\BaseAction;
use Vima\CodeIgniter\Support\Utils;
use Vima\Core\Permission\Contracts\PermissionRepositoryInterface;

class ListAction extends BaseAction
{
    public function execute(array $params): void
    {
        $limit = (int) (isset($params['limit']) ? $params['limit'] : (CLI::getOption('limit') ?? 30));

        /** @var PermissionRepositoryInterface $permRepo */
        $permRepo = service('vima_permissions');
        $permissions = $permRepo->all();

        if (empty($permissions)) {
            CLI::write('No permissions found.', 'yellow');
            return;
        }

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
    }

    public function getDescription(): string
    {
        return 'List all permissions';
    }

    public function getUsage(): string
    {
        return 'vima permission list [options]';
    }
}
