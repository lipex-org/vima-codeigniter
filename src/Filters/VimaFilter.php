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

class VimaFilter implements FilterInterface
{
    /**
     * @param array|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments)) {
            return;
        }

        $vima = service('vima');

        // Try to get current user from common CI4 auth systems
        $user = null;
        if (function_exists('auth')) {
            $user = auth()->user();
        } elseif (service('auth')->user()) {
            $user = service('auth')->user();
        }

        if (!$user) {
            return response()->setStatusCode(401);
        }

        foreach ($arguments as $permission) {
            if (!$vima->can($user, $permission)) {
                return response()->setStatusCode(403)->setBody('Forbidden: Missing permission ' . $permission);
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
