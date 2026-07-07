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
use Vima\Core\Role\Services\RoleService;
use function Vima\Core\resolve;

class ListAction extends BaseAction
{
    public function execute(array $params): void
    {
        $limit = (int) (isset($params['limit']) ? $params['limit'] : (CLI::getOption('limit') ?? 30));
        $resolve = (bool) (CLI::getOption('resolve') ?? CLI::getOption('R') ?? false);

        /** @var RoleService $roleM */
        $roleM = resolve(RoleService::class);
        $roles = $roleM->all(resolve: $resolve);

        if (empty($roles)) {
            CLI::write('No roles found.', 'yellow');
            return;
        }

        $body = [];
        foreach ($roles as $role) {
            $parents = implode(', ', array_map(fn($r) => $r->namespace ? "{$r->namespace}:{$r->name}" : $r->name, $role->parents)) ?: '[--NONE--]';
            $permissions = implode(', ', array_map(fn($p) => $p->namespace ? "{$p->namespace}:{$p->name}" : $p->name, $role->permissions)) ?: '[--NONE--]';

            $row = [
                $role->id,
                $role->namespace ?? '[--GLOBAL--]',
                $role->name,
                Utils::truncate($role->description, $limit),
                Utils::truncate(json_encode($role->context), $limit),
            ];

            if ($resolve) {
                $row = [
                    ...$row,
                    ...[
                        Utils::truncate($permissions, $limit),
                        Utils::truncate($parents, $limit),
                    ]
                ];
            }

            $body[] = $row;
        }

        $thead = ['ID', 'Namespace', 'Name', 'Description', 'Context'];

        if ($resolve) {
            $thead[] = 'Permissions';
            $thead[] = 'Parents';
        }

        CLI::table($body, $thead);
    }

    public function getDescription(): string
    {
        return 'List all roles';
    }

    public function getUsage(): string
    {
        return 'vima role list [options]';
    }

    public function getOptions(): array
    {
        return [
            '--limit'   => 'Limit description/context/permission characters length (default: 30).',
            '--resolve' => 'Resolve and display related permissions and parents.',
            '-R'        => 'Alias for --resolve.',
        ];
    }
}
