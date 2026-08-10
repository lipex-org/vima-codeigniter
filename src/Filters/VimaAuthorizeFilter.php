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
use Vima\Core\User\Services\UserResolutionService;
use function Vima\Core\resolve;

/**
 * Filter to enforce Vima policies.
 */
class VimaAuthorizeFilter implements FilterInterface
{
    /**
     * @param array|null $arguments [permission]
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments)) {
            return;
        }

        $permission = $arguments[0] ?? null;
        $page = $arguments[1] ?? null;

        if (!$permission) {
            return;
        }
        /**
         * @var Vima $config
         */
        $config = config('Vima');

        // Use the can() helper which handles user resolution and vima_context
        $resource = vima_context();
        if (!can($permission, $resource)) {
            if ($page) {
                return redirect()->to($page);
            }

            $statusCode = $config->getDenyStatusCode();
            $errorMsg = ($statusCode === 404) ? 'Resource not found' : 'Access denied';

            // Check if request expects JSON
            if (stripos($request->getHeaderLine('Accept'), 'application/json') !== false || $request->isAJAX()) {
                return Services::response()
                    ->setStatusCode($statusCode)
                    ->setJSON([
                        'error' => $errorMsg,
                        'message' => ($statusCode === 404) 
                            ? "The requested resource could not be found."
                            : "Access denied on permission '{$permission}'"
                    ]);
            }

            $viewPath = $config->getErrorView($statusCode);

            return Services::response()
                ->setStatusCode($statusCode)
                ->setBody(view($viewPath));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
