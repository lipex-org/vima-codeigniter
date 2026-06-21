<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Commands\Actions\Cache;

use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Commands\Actions\VimaActionInterface;
use Vima\CodeIgniter\Commands\Actions\BaseAction;

class ClearAction extends BaseAction
{
    public function execute(array $params): void
    {
        CLI::write('Clearing Vima caches...', 'yellow');

        try {
            service('vima_deployment')->clear();
            CLI::write('Vima caches flushed successfully.', 'green');
        } catch (\Throwable $e) {
            CLI::error('Failed to clear Vima cache: ' . $e->getMessage());
        }
    }

    public function getDescription(): string
    {
        return 'Flush all Vima caches';
    }

    public function getUsage(): string
    {
        return 'vima cache clear';
    }
}
