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
     * Retrieves all discovered policy class names, checking cache and autoDiscover settings.
     *
     * @param string|null $directory The directory name to scan (e.g., 'Policies')
     * @return array<class-string>
     */
    public static function getPolicies(?string $directory = null): array
    {
        $config = config('Vima');
        if ($config && !($config->policies['autoDiscover'] ?? true)) {
            return [];
        }

        $directory = $directory ?? ($config->policies['directory'] ?? 'Policies');
        $cacheActive = $config && $config->isCacheEnabled();
        $cache = null;
        $prefix = rtrim($config->cache['prefix'] ?? 'vima_', '_:') ?: 'vima';
        $cacheKey = $prefix . '_policies_discovered';

        // 1. Return from cache if enabled
        if ($cacheActive) {
            try {
                $cache = service('vima_cache');
                $discovered = $cache->get($cacheKey);
                if (is_array($discovered)) {
                    return $discovered;
                }
            } catch (\Throwable $e) {
                $cacheActive = false;
            }
        }

        // 2. Discover via FileLocator
        $discovered = [];
        try {
            $locator = Services::locator();
            $files = $locator->listFiles($directory);

            foreach ($files as $file) {
                $className = $locator->getClassname($file);

                if (!$className || !class_exists($className) || (new \ReflectionClass($className))->isAbstract()) {
                    continue;
                }

                if (is_subclass_of($className, PolicyInterface::class)) {
                    if (!in_array($className, $discovered, true)) {
                        $discovered[] = $className;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fallback if services/locator are not fully loaded during early CLI init
        }

        // 3. Store in cache if active
        if ($cacheActive && $cache !== null && !empty($discovered)) {
            try {
                $cache->set($cacheKey, $discovered, $config->cache['ttl'] ?? 3600);
            } catch (\Throwable $e) {
                // Fallback
            }
        }

        return $discovered;
    }

    /**
     * Discovers and registers policies in the given namespace/directory.
     *
     * @param string $directory The directory name to scan (e.g., 'Policies')
     * @return void
     */
    public static function discoverPolicies(string $directory = 'Policies'): void
    {
        $policies = self::getPolicies($directory);

        if (empty($policies)) {
            return;
        }

        /** @var PolicyRegistryInterface $registry */
        $registry = resolve(PolicyRegistryInterface::class);

        foreach ($policies as $className) {
            if (class_exists($className) && is_subclass_of($className, PolicyInterface::class)) {
                $registry->registerClass($className::getResource(), $className);
            }
        }
    }
}
