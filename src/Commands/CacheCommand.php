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

use Vima\CodeIgniter\Commands\Actions\Cache\ClearAction;

class CacheCommand extends ResourceProxyCommand
{
    protected $name = 'vima:cache';
    protected $description = 'Manage Vima cache';
    protected string $resource = 'cache';

    protected array $actions = [
        'clear' => ClearAction::class,
    ];
}
