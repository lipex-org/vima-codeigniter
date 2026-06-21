<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Commands;

use Vima\CodeIgniter\Commands\Actions\Role\CreateAction;
use Vima\CodeIgniter\Commands\Actions\Role\ListAction;
use Vima\CodeIgniter\Commands\Actions\Role\AssignAction;
use Vima\CodeIgniter\Commands\Actions\Role\PermitAction;

class RoleCommand extends ResourceProxyCommand
{
    protected $name = 'vima:role';
    protected $description = 'Manage Vima roles';
    protected string $resource = 'role';

    protected array $actions = [
        'create' => CreateAction::class,
        'list' => ListAction::class,
        'assign' => AssignAction::class,
        'permit' => PermitAction::class,
    ];
}
