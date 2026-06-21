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

use Vima\CodeIgniter\Commands\Actions\User\DenyAction;
use Vima\CodeIgniter\Commands\Actions\User\UndenyAction;
use Vima\CodeIgniter\Commands\Actions\User\PermitAction;
use Vima\CodeIgniter\Commands\Actions\User\DeniesAction;
use Vima\CodeIgniter\Commands\Actions\User\PermissionsAction;
use Vima\CodeIgniter\Commands\Actions\User\RolesAction;

class UserCommand extends ResourceProxyCommand
{
    protected $name = 'vima:user';
    protected $description = 'Manage Vima user access and roles';
    protected string $resource = 'user';

    protected array $actions = [
        'deny' => DenyAction::class,
        'undeny' => UndenyAction::class,
        'permit' => PermitAction::class,
        'denies' => DeniesAction::class,
        'permissions' => PermissionsAction::class,
        'roles' => RolesAction::class,
    ];
}
