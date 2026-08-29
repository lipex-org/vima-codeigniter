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

use Vima\CodeIgniter\Commands\Actions\Policy\CreateAction;
use Vima\CodeIgniter\Commands\Actions\Policy\ListAction;
use Vima\CodeIgniter\Commands\Actions\Policy\CacheAction;

class PolicyCommand extends ResourceProxyCommand
{
    protected $name = 'vima:policy';
    protected $description = 'Manage Vima policies';
    protected string $resource = 'policy';

    protected array $actions = [
        'create' => CreateAction::class,
        'list'   => ListAction::class,
        'cache'  => CacheAction::class,
    ];
}
