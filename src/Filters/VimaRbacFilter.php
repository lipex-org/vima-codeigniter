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

namespace Vima\CodeIgniter\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Vima\CodeIgniter\Config\Vima;
use Config\Services;
use Vima\Core\User\Services\UserService;
use function Vima\Core\resolve;

/**
 * Filter to enforce Vima RBAC roles.
 */
class VimaRbacFilter implements FilterInterface
{
    /**
     * @param array|null $arguments [role, redirectPage]
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments)) {
            return;
        }

        $role = $arguments[0] ?? null;
        $page = $arguments[1] ?? null;

        if (!$role) {
            return;
        }

        /**
         * @var Vima $config
         */
        $config = config('Vima');

        // Resolve current user
        $user = null;
        if ($config) {
            $user = $config->getCurrentUser();
        } else {
            try {
                if (function_exists('auth')) {
                    $user = auth()->user();
                } else {
                    $user = service('auth')->user();
                }
            } catch (\Throwable $e) {
                $user = null;
            }
        }

        if (!$user) {
            if ($page) {
                return redirect()->to($page);
            }
            if (stripos($request->getHeaderLine('Accept'), 'application/json') !== false || $request->isAJAX()) {
                return Services::response()
                    ->setStatusCode(401)
                    ->setJSON([
                        'error' => 'Unauthorized',
                        'message' => 'User context not found'
                    ]);
            }
            $viewPath = $config->view403 ?? 'Vima\CodeIgniter\Views\error_403';
            return Services::response()
                ->setStatusCode(403)
                ->setBody(view($viewPath));
        }

        $userService = resolve(UserService::class);
        $hasRole = $userService->user($user)->has()->role($role);

        if (!$hasRole) {
            if ($page) {
                return redirect()->to($page);
            }

            // Check if request expects JSON
            if (stripos($request->getHeaderLine('Accept'), 'application/json') !== false || $request->isAJAX()) {
                return Services::response()
                    ->setStatusCode(403)
                    ->setJSON([
                        'error' => 'Access denied',
                        'message' => "Access denied on role '{$role}'"
                    ]);
            }

            $viewPath = $config->view403 ?? 'Vima\CodeIgniter\Views\error_403';
            return Services::response()
                ->setStatusCode(403)
                ->setBody(view($viewPath));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
