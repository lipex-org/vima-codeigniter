<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Vima\CodeIgniter\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filter to register policies dynamically.
 * Useful for registering policies only for specific routes.
 */
class VimaPolicyFilter implements FilterInterface
{
    /**
     * @param array|null $arguments [action, callback] 
     * Note: CI4 arguments are strings. For complex logic, 
     * better to use a dedicated policy class or register in Config.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments)) {
            return;
        }

        // Usually arguments in filters are strings like 'actionName:ClassName::method'
        foreach ($arguments as $arg) {
            if (strpos($arg, ':') !== false) {
                [$action, $callableStr] = explode(':', $arg, 2);

                // Allow shortcut like 'posts.edit:App\Policies\PostPolicy::canEdit'
                vima_policy($action, function (...$args) use ($callableStr) {
                    [$class, $method] = explode('::', $callableStr);
                    return (new $class())->$method(...$args);
                });
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
