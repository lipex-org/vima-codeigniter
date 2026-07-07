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
use CodeIgniter\Config\Services;
use Vima\CodeIgniter\Commands\Actions\BaseAction;
use Vima\CodeIgniter\Support\Discovery;
use Vima\Core\Policy\Contracts\PolicyInterface;
use Vima\Core\Policy\Contracts\PolicyRegistryInterface;
use function Vima\Core\resolve;

class ListAction extends BaseAction
{
    public function execute(array $params): void
    {
        $config = config('Vima');
        $registered = $config->policies['registered'] ?? [];

        // Run existing autodiscovery to populate the registry
        $directory = $config->policies['directory'] ?? 'Policies';
        Discovery::discoverPolicies($directory);

        /** @var PolicyRegistryInterface $registry */
        $registry = resolve(PolicyRegistryInterface::class);
        $registeredClasses = $registry->getRegisteredClasses();

        // Unique policy classes from registry (which now has config + discovered)
        $allPolicies = array_unique(array_values($registeredClasses));
        sort($allPolicies);

        if (empty($allPolicies)) {
            CLI::write('No policies found.', 'yellow');
            return;
        }

        $body = [];
        foreach ($allPolicies as $policyClass) {
            $isRegistered = in_array($policyClass, $registered, true);

            // If it is in the registry, it's either from Config or Autodiscovery.
            // We can check if it implements PolicyInterface to confirm it was successfully discovered
            $isDiscovered = class_exists($policyClass) && is_subclass_of($policyClass, PolicyInterface::class);

            $source = 'Both';
            if ($isRegistered && !$isDiscovered) {
                $source = 'Config';
            } elseif (!$isRegistered && $isDiscovered) {
                $source = 'Discovered';
            }

            $resource = 'N/A';
            if (class_exists($policyClass)) {
                if (is_subclass_of($policyClass, PolicyInterface::class)) {
                    $resource = $policyClass::getResource();
                } else {
                    $resource = 'Does not implement PolicyInterface';
                }
            } else {
                $resource = 'Class not found';
            }

            $body[] = [
                $policyClass,
                $resource,
                $source,
            ];
        }

        CLI::table($body, ['Policy Class', 'Resource Class', 'Source']);
    }

    public function getDescription(): string
    {
        return 'List all registered and autodiscovered policies';
    }

    public function getUsage(): string
    {
        return 'vima:policy list';
    }
}
