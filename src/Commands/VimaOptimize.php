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
    protected $usage = 'vima:optimize';

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        CLI::write('Warming up Vima caches...', 'yellow');

        try {
            $stats = service('vima_deployment')->optimize();

            CLI::write('Optimization complete!', 'green');
            CLI::write("  - Cached {$stats['roles']} roles.");
            CLI::write("  - Cached {$stats['policies']} policy maps.");

        } catch (\Throwable $e) {
            CLI::error('Optimization failed: ' . $e->getMessage());
        }
    }
}
