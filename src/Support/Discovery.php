<?php

namespace Vima\CodeIgniter\Support;

use CodeIgniter\Config\Services;
use Vima\Core\Policy\Contracts\PolicyInterface;
use Vima\Core\Policy\Contracts\PolicyRegistryInterface;
use function Vima\Core\resolve;

/**
 * Class Discovery
 * 
 * Handles auto-discovery of Vima components in a CodeIgniter 4 application.
 */
class Discovery
{
    /**
     * Discovers and registers policies in the given namespace/directory.
     *
     * @param string $directory The directory name to scan (e.g., 'Policies')
     * @return void
     */
    public static function discoverPolicies(string $directory = 'Policies'): void
    {
        $config = config('Vima');
        $cacheActive = $config && $config->isCacheEnabled();
        $cache = null;
        $discovered = null;

        if ($cacheActive) {
            try {
                $cache = service('vima_cache');
                $discovered = $cache->get('vima:policies:discovered');
            } catch (\Throwable $e) {
                $cacheActive = false;
            }
        }

        /** @var PolicyRegistryInterface $registry */
        $registry = resolve(PolicyRegistryInterface::class);

        if (is_array($discovered)) {
            foreach ($discovered as $className) {
                if (class_exists($className) && is_subclass_of($className, PolicyInterface::class)) {
                    $registry->registerClass($className::getResource(), $className);
                }
            }
            return;
        }

        $locator = Services::locator();
        $files = $locator->listFiles($directory);
        $discovered = [];

        foreach ($files as $file) {
            $className = $locator->getClassname($file);

            if (!$className || !class_exists($className) || (new \ReflectionClass($className))->isAbstract()) {
                continue;
            }

            if (is_subclass_of($className, PolicyInterface::class)) {
                $registry->registerClass($className::getResource(), $className);
                $discovered[] = $className;
            }
        }

        if ($cacheActive && $cache !== null) {
            $cache->set('vima:policies:discovered', $discovered, $config->cache['ttl'] ?? 3600);
        }
    }
}
