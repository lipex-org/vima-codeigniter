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

class DeniesAction extends BaseAction
{
    public function execute(array $params): void
    {
        $user_id = $params[0] ?? CLI::prompt('User ID', null, 'required');
        $limit = (int) (isset($params['limit']) ? $params['limit'] : (CLI::getOption('limit') ?? 30));

        if (empty($user_id)) {
            CLI::error('User ID is required.');
            return;
        }

        $user = Utils::creatVimaUser($user_id);

        $userIdResolved = \Vima\Core\Vima::user($user)->getId();
        CLI::write("Permission Denials for User: " . $userIdResolved, 'yellow');

        try {
            $denies = \Vima\Core\Vima::user($user)->get()->denies()->permission();

            if (empty($denies)) {
                CLI::write('No explicit denials found for this user.', 'green');
                return;
            }

            $body = [];
            foreach ($denies as $deny) {
                $perm = \Vima\Core\Vima::permissions()->find($deny->permissionId);
                $body[] = [
                    $deny->id,
                    $perm ? ($perm->namespace ?? '[--GLOBAL--]') : 'N/A',
                    $perm ? $perm->name : $deny->permissionId,
                    $perm ? Utils::truncate($perm->description, $limit) : 'N/A',
                    Utils::truncate($deny->reason, $limit),
                ];
            }

            CLI::table($body, ['ID', 'Namespace', 'Name', 'Description', 'Reason']);
        } catch (\Exception $e) {
            CLI::error($e->getMessage());
        }
    }

    public function getDescription(): string
    {
        return 'List all explicit permission denials for a user';
    }

    public function getUsage(): string
    {
        return 'vima user denies [user_id]';
    }

    public function getOptions(): array
    {
        return [
            '--limit' => 'Limit description and reason characters length (default: 30).',
        ];
    }
}
