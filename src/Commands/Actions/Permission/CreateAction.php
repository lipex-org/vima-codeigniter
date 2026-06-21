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
use Vima\Core\AccessManagers\AccessManager;
use Vima\Core\Permission\Entities\Permission;
use Vima\Core\Vima;

class CreateAction extends BaseAction
{
    public function execute(array $params): void
    {
        $name = $params[0] ?? CLI::prompt('Permission name');
        $namespace = CLI::getOption('namespace') ?? CLI::getOption('N');
        $description = CLI::getOption('description') ?? CLI::getOption('D') ?? $params[1] ?? '';

        try {
            /** @var AccessManager $manager */
            $p = Permission::define($name, namespace: $namespace, description: $description);
            $permission = Vima::permissions()->save($p);

            $msg = "Permission [{$permission->name}]";
            if ($permission->namespace) {
                $msg .= " in namespace [{$permission->namespace}]";
            }
            $msg .= " created successfully.";

            CLI::write($msg, 'green');
        } catch (\Throwable $e) {
            CLI::error("Failed to create permission: " . $e->getMessage());
        }
    }

    public function getDescription(): string
    {
        return 'Create a new permission';
    }

    public function getUsage(): string
    {
        return 'vima permission create [name] [options]';
    }
}
