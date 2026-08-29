<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Commands\Actions\Policy;

use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Commands\Actions\BaseAction;
use Vima\CodeIgniter\Support\Discovery;
use Vima\Core\Policy\Contracts\PolicyInterface;
use Vima\Core\Policy\Contracts\PolicyRegistryInterface;
use function Vima\Core\resolve;

class CacheAction extends BaseAction
{
    public function execute(array $params): void
    {
        $clear = $this->getOption('clear', $params, false);

        /** @var PolicyRegistryInterface $registry */
        $registry = resolve(PolicyRegistryInterface::class);

        if ($clear) {
            CLI::write('Clearing cached policy mappings...', 'yellow');
            $registry->clearCache();
            CLI::write('Policy mappings cache cleared successfully.', 'green');
            return;
        }

        CLI::write('Discovering and caching policies...', 'yellow');

        $config = config('Vima');
        $directory = $config->policies['directory'] ?? 'Policies';
        Discovery::discoverPolicies($directory);

        $stats = $registry->warmCache(force: true);

        if (empty($stats)) {
            CLI::write('No policy classes found to cache.', 'yellow');
            return;
        }

        $body = [];
        foreach ($stats as $policyClass => $mappedCount) {
            $resource = (class_exists($policyClass) && is_subclass_of($policyClass, PolicyInterface::class))
                ? $policyClass::getResource()
                : 'N/A';

            $body[] = [
                $policyClass,
                $resource,
                $mappedCount . ' mapped ability/methods',
            ];
        }

        CLI::table($body, ['Policy Class', 'Resource Class', 'Attribute Mappings']);
        CLI::write('Successfully cached ' . count($stats) . ' policy classes!', 'green');
    }

    public function getOptions(): array
    {
        return [
            '--clear' => 'Clear all cached policy mappings instead of warming.',
        ];
    }

    public function getDescription(): string
    {
        return 'Pre-warm and cache all discovered and registered policy attribute mappings';
    }

    public function getUsage(): string
    {
        return 'vima:policy cache [--clear]';
    }
}
