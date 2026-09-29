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

class VimaOptimize extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Vima';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'vima:optimize';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Optimize Vima performance by pre-warming caches';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'vima:optimize [options]';

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--users'   => 'Comma-separated user IDs to pre-warm matrices for (e.g. --users=1,2,5)',
        '--filter'  => 'Comma-separated permission names to filter matrix compilation (e.g. --filter=posts.create,posts.edit)',
        '--no-clear'=> 'Do not clear caches before optimizing',
    ];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        CLI::write('⚡ Warming up Vima caches...', 'yellow');

        try {
            $usersInput = CLI::getOption('users') ?? $params['users'] ?? null;
            $filterInput = CLI::getOption('filter') ?? $params['filter'] ?? null;

            $users = [];
            if (!empty($usersInput)) {
                $users = array_map('trim', explode(',', (string) $usersInput));
            }

            $matrixFilter = [];
            if (!empty($filterInput)) {
                $matrixFilter = array_map('trim', explode(',', (string) $filterInput));
            }

            $stats = service('vima_deployment')->optimize($users, $matrixFilter);

            CLI::write('✔ Optimization complete!', 'green');
            CLI::write("  • Cached {$stats['permissions']} system permissions.");
            CLI::write("  • Pre-compiled {$stats['roles']} role hierarchies & permission trees.");
            CLI::write("  • Pre-compiled {$stats['policies']} policy attribute maps.");
            if (!empty($stats['users'])) {
                CLI::write("  • Pre-compiled matrices for {$stats['users']} active users.", 'cyan');
            }

        } catch (\Throwable $e) {
            CLI::error('Optimization failed: ' . $e->getMessage());
        }
    }
}
