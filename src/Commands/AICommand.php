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

use Vima\CodeIgniter\Commands\Actions\Publish\AIAction;

class AICommand extends ResourceProxyCommand
{
    protected $name = 'vima:ai';
    protected $description = 'Publish AI assistant rules and guidance';
    protected string $resource = 'ai';

    protected array $actions = [
        'publish' => AIAction::class,
    ];
}
