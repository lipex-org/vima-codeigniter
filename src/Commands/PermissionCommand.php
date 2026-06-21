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

use Vima\CodeIgniter\Commands\Actions\Permission\CreateAction;
use Vima\CodeIgniter\Commands\Actions\Permission\ListAction;

class PermissionCommand extends ResourceProxyCommand
{
    protected $name = 'vima:permission';
    protected $description = 'Manage Vima permissions';
    protected string $resource = 'permission';

    protected array $actions = [
        'create' => CreateAction::class,
        'list' => ListAction::class,
    ];
}
