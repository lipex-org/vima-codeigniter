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

use Vima\CodeIgniter\Commands\Actions\Generate\MapsAction;

class MapsCommand extends ResourceProxyCommand
{
    protected $name = 'vima:maps';
    protected $description = 'Generate Vima assets';
    protected string $resource = 'maps';

    protected array $actions = [
        'generate' => MapsAction::class,
    ];
}
