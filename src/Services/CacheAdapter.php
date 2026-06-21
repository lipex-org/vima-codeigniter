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

namespace Vima\CodeIgniter\Services;

use CodeIgniter\Cache\CacheInterface as CICacheInterface;
use Vima\Core\Cache\Contracts\CacheInterface;

/**
 * Class CacheAdapter
 *
 * Adapts CodeIgniter's Cache service to Vima's CacheInterface.
 *
 * @package Vima\CodeIgniter\Services
 */
class CacheAdapter implements CacheInterface
{
    /**
     * @param CICacheInterface $cache
     */
    public function __construct(
        protected CICacheInterface $cache
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->cache->get($this->sanitizeKey($key));
        
        if (ENVIRONMENT !== 'testing') {
            try {
                $session = session();
                if ($value !== null) {
                    $session->set('simulated_cache_hits', ($session->get('simulated_cache_hits') ?? 0) + 1);
                } else {
                    $session->set('simulated_cache_misses', ($session->get('simulated_cache_misses') ?? 0) + 1);
                }
            } catch (\Throwable $e) {
                // Ignore session failures in CLI / bootstrap phases
            }
        }

        return $value === null ? $default : $value;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        if (ENVIRONMENT !== 'testing') {
            try {
                $session = session();
                $session->set('simulated_cache_writes', ($session->get('simulated_cache_writes') ?? 0) + 1);
            } catch (\Throwable $e) {
                // Ignore
            }
        }
        return $this->cache->save($this->sanitizeKey($key), $value, $ttl ?? 3600);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        return $this->cache->delete($this->sanitizeKey($key));
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        return $this->cache->clean();
    }

    /**
     * Sanitize cache key for CI4 compatibility (reserved: {}()/\@:)
     */
    protected function sanitizeKey(string $key): string
    {
        return str_replace(['{', '}', '(', ')', '/', '\\', '@', ':'], '_', $key);
    }
}
