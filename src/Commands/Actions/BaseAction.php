<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Commands\Actions;

use CodeIgniter\CLI\CLI;

/**
 * Class BaseAction
 * 
 * Provides common utilities for Vima actions.
 */
abstract class BaseAction implements VimaActionInterface
{
    /**
     * Extracts an option value from parameters or CLI.
     * 
     * @param string $name
     * @param array $params
     * @param mixed $default
     * @return mixed
     */
    protected function getOption(string $name, array &$params, $default = null)
    {
        // 1. Check named key in params (from command array syntax)
        if (array_key_exists($name, $params)) {
            $val = $params[$name];
            unset($params[$name]);
            return $val ?? true;
        }

        // 2. Check CLI global state (spark handles this for real commands)
        $val = CLI::getOption($name);
        if ($val !== null)
            return $val;

        // 3. Manual parse from params array (from command string syntax)
        $found = false;
        $val = null;
        foreach ($params as $key => $param) {
            if ($param === "--{$name}") {
                unset($params[$key]);
                $found = true;
                $val = true;
                // Check if next key exists and isn't another option
                $keys = array_keys($params);
                $currIdx = array_search($key, $keys);
                $nextKey = $keys[$currIdx] ?? null; // since $key was unset, $currIdx points to the next element
                if ($nextKey !== null) {
                    $next = $params[$nextKey];
                    if (!str_starts_with($next, '-')) {
                        $val = $next;
                        unset($params[$nextKey]);
                    }
                }
                break;
            }
            if (is_string($param) && str_starts_with($param, "--{$name}=")) {
                unset($params[$key]);
                $found = true;
                $val = substr($param, strlen($name) + 3);
                break;
            }
        }
        if ($found) {
            $params = array_values($params);
            return $val;
        }

        return $default;
    }

    /**
     * Returns the options for the action.
     * 
     * @return array
     */
    public function getOptions(): array
    {
        return [];
    }
}
