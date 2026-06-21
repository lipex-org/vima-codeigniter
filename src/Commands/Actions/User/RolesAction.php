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

class RolesAction extends BaseAction
{
    public function execute(array $params): void
    {
        $user_id = $params[0] ?? CLI::prompt('User ID', null, 'required');
        $resolve = (bool) CLI::getOption('resolve');
        $limit = (int) (isset($params['limit']) ? $params['limit'] : (CLI::getOption('limit') ?? 30));

        if (empty($user_id)) {
            CLI::write('User ID is required.', 'red');
            return;
        }

        $user = Utils::creatVimaUser($user_id);

        try {
            $userSession = \Vima\Core\Vima::user($user);
            $roles = $userSession->get()->roles($resolve);

            CLI::write('Roles for user ' . $user_id . ':', 'green');

            $body = [];
            foreach ($roles as $role) {
                $parents = implode(', ', array_map(fn($r) => $r->namespace ? "{$r->namespace}:{$r->name}" : $r->name, $role->parents)) ?: '[--NONE--]';
                $children = implode(', ', array_map(fn($r) => $r->namespace ? "{$r->namespace}:{$r->name}" : $r->name, $role->children)) ?: '[--NONE--]';
                $permissions = implode(', ', array_map(fn($p) => $p->namespace ? "{$p->namespace}:{$p->name}" : $p->name, $role->permissions)) ?: '[--NONE--]';

                $row = [
                    $this->truncate($role->id, $limit),
                    $this->truncate($role->namespace ?? '[--GLOBAL--]', $limit),
                    $this->truncate($role->name, $limit),
                    $this->truncate($role->description, $limit),
                    $this->truncate(json_encode($role->context ?? []), $limit),
                ];

                if ($resolve) {
                    $row = [
                        ...$row,
                        $this->truncate($permissions, $limit),
                        $this->truncate($parents, $limit),
                        $this->truncate($children, $limit),
                    ];
                }

                $body[] = $row;
            }

            $thead = ['ID', 'Namespace', 'Name', 'Description', 'Context'];

            if ($resolve) {
                $thead = array_merge($thead, ['Permissions', 'Parents', 'Children']);
            }

            CLI::table($body, $thead);
        } catch (\Exception $e) {
            CLI::write($e->getMessage(), 'red');
        }
    }

    private function truncate(string $text, int $limit): string
    {
        if (strlen($text) <= $limit) {
            return $text;
        }

        return substr($text, 0, $limit - 3) . '...';
    }

    public function getDescription(): string
    {
        return 'List all roles for a user';
    }

    public function getUsage(): string
    {
        return 'vima user roles [user_id]';
    }
}
