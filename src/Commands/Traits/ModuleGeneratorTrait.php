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

namespace Vima\CodeIgniter\Commands\Traits;

use Config\Services;

trait ModuleGeneratorTrait
{
    /**
     * Resolves a namespace path by checking the autoloader configuration.
     *
     * @param string $namespace The namespace to resolve (e.g. 'App\Policies')
     * @return string|null The resolved folder path, or null if not matched
     */
    protected function resolveNamespacePath(string $namespace): ?string
    {
        $autoloader = Services::autoloader();
        $namespaces = $autoloader->getNamespace();

        // Sort namespaces by length descending to match longest prefix first
        uksort($namespaces, fn($a, $b) => strlen($b) <=> strlen($a));

        $normalizedNs = trim($namespace, '\\') . '\\';

        foreach ($namespaces as $prefix => $paths) {
            $prefix = trim($prefix, '\\') . '\\';
            if (stripos($normalizedNs, $prefix) === 0) {
                $subPath = substr($normalizedNs, strlen($prefix));
                $basePath = rtrim($paths[0], '/\\');
                $fullPath = $basePath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, trim($subPath, '\\'));
                return rtrim($fullPath, DIRECTORY_SEPARATOR);
            }
        }

        return null;
    }

    /**
     * Parses a class name and returns its namespace and class name.
     * Supports fully qualified namespaces in the class argument.
     *
     * @param string $class The class name or fully qualified path (e.g., 'Vima\CustomModule\Policies\MyPolicy' or 'MyPolicy')
     * @param string $defaultNamespace The namespace to fallback to
     * @return array [namespace, className]
     */
    protected function parseClassName(string $class, string $defaultNamespace): array
    {
        $class = str_replace('/', '\\', $class);

        if (strpos($class, '\\') !== false) {
            $parts = explode('\\', $class);
            $className = array_pop($parts);
            $namespace = implode('\\', $parts);
            return [$namespace, $className];
        }

        return [$defaultNamespace, $class];
    }
}
