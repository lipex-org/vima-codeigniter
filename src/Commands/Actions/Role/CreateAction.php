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
use Vima\CodeIgniter\Commands\Actions\VimaActionInterface;
use Vima\CodeIgniter\Commands\Actions\BaseAction;
use Vima\Core\Permission\Entities\Permission;
use Vima\Core\Role\Entities\Role;

class CreateAction extends BaseAction
{
    public function execute(array $params): void
    {
        $name = $params[0] ?? CLI::prompt('Role name');
        $namespace = CLI::getOption('namespace') ?? CLI::getOption('N');
        $description = CLI::getOption('description') ?? CLI::getOption('D') ?? $params[1] ?? '';

        $parents = array_filter(array_map('trim', explode(',', CLI::getOption('parents') ?? '')));
        $children = array_filter(array_map('trim', explode(',', CLI::getOption('children') ?? '')));
        $permissions = array_filter(array_map('trim', explode(',', CLI::getOption('permissions') ?? '')));
        $context = [];

        try {
            $contextString = CLI::getOption('context');
            $context = $contextString ? json_decode($contextString, true) : [];
        } catch (\Throwable $e) {
            CLI::error("Failed to parse context: " . $e->getMessage());
            return;
        }

        try {
            $role = Role::define(
                name: $name,
                namespace: $namespace,
                description: $description
            );
            $role->context = $context;

            $savedRole = \Vima\Core\Vima::roles()->save($role);

            // Add permissions
            foreach ($permissions as $permName) {
                $p = \Vima\Core\Vima::permissions()->find($permName);
                if (!$p) {
                    $p = Permission::define($permName);
                    $p = \Vima\Core\Vima::permissions()->save($p);
                }
                \Vima\Core\Vima::role($savedRole)->permissions()->add($p->id);
            }

            // Add parents
            foreach ($parents as $parentName) {
                $pRole = \Vima\Core\Vima::roles()->find($parentName);
                if (!$pRole) {
                    $pRole = Role::define($parentName);
                    $pRole = \Vima\Core\Vima::roles()->save($pRole);
                }
                \Vima\Core\Vima::role($savedRole)->parents()->add($pRole->id);
            }

            $msg = "Role [{$savedRole->name}]";
            if ($savedRole->namespace) {
                $msg .= " in namespace [{$savedRole->namespace}]";
            }
            $msg .= " created successfully.";

            CLI::write($msg, 'green');
        } catch (\Throwable $e) {
            CLI::error("Failed to create role: " . $e->getMessage());
        }
    }

    public function getDescription(): string
    {
        return 'Create a new role';
    }

    public function getUsage(): string
    {
        return 'vima role create [name] [options]';
    }
}
