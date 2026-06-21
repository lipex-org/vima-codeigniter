<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Vima\Core\Services\SyncService;
use Vima\Core\Config\VimaConfig;

class VimaSync extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:sync';
    protected $description = 'Sync permissions and roles from config to storage';
    protected $usage = 'vima:sync [options]';
    protected $options = [
        'refresh' => 'Wipe existing roles and permissions before syncing',
    ];

    public function run(array $params)
    {
        $refresh = isset($params['refresh']) || CLI::getOption('refresh');

        CLI::write('Initializing Vima Sync...', 'yellow');

        try {
            /** @var SyncService $syncService */
            $syncService = service('vima_sync');

            if ($refresh) {
                $syncService->refresh();
                CLI::write('Refresh mode enabled.', 'cyan');
            }

            /** @var VimaConfig $config */
            $config = service('vima_config');

            CLI::write('Syncing definitions...', 'yellow');
            $response = $syncService->sync();

            CLI::write('Sync complete.', 'green');

            if ($response?->warn) {
                foreach ($response->skipped->roles as $name => $reason) {
                    CLI::write("  [Role] Skipped $name: $reason", 'yellow');
                }
                foreach ($response->skipped->permissions as $name => $reason) {
                    CLI::write("  [Permission] Skipped $name: $reason", 'yellow');
                }
            }

            // Also trigger map generation if everything went well
            CLI::write('Triggering map generation...', 'yellow');
            $this->call('vima:maps', ['generate']);

        } catch (\Throwable $e) {
            CLI::error('Sync failed: ' . $e->getMessage());
            log_message('error', '[Vima] Sync failed: ' . $e->getMessage());
        }
    }
}
