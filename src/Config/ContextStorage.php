<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Vima\CodeIgniter\Config;

/**
 * Request-scoped context storage for ABAC resource checks.
 * This class holds the resource object being checked in the current request.
 */
class ContextStorage
{
    private ?object $data = null;

    /**
     * Set the current context resource.
     * 
     * @param object|null $obj
     * @return void
     */
    public function set(?object $obj): void
    {
        $this->data = $obj;
    }

    /**
     * Get the current context resource.
     * 
     * @return object|null
     */
    public function get(): ?object
    {
        return $this->data;
    }
}
