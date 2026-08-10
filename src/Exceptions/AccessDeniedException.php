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

namespace Vima\CodeIgniter\Exceptions;

use CodeIgniter\HTTP\ResponsableInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Vima\Core\Exceptions\AccessDeniedException as CoreAccessDeniedException;

/**
 * CodeIgniter 4 specific Access Denied Exception.
 */
class AccessDeniedException extends CoreAccessDeniedException implements ResponsableInterface
{
    /**
     * Generate the HTTP response when this exception is thrown.
     */
    public function getResponse(): ResponseInterface
    {
        $collector = new ResponseCollector();
        
        // Trigger CI4 event so other packages (e.g., Inertia adapter) can provide a custom response
        \CodeIgniter\Events\Events::trigger('vima.access_denied_response', $this, $collector);

        if ($collector->getResponse() !== null) {
            return $collector->getResponse();
        }

        $config = config('Vima');
        $statusCode = $config->getDenyStatusCode();
        $errorMsg = ($statusCode === 404) ? 'Resource not found' : 'Access denied';

        $request = service('request');
        $response = service('response');

        if (stripos($request->getHeaderLine('Accept'), 'application/json') !== false || $request->isAJAX()) {
            return $response
                ->setStatusCode($statusCode)
                ->setJSON([
                    'error' => $errorMsg,
                    'message' => ($statusCode === 404) 
                        ? "The requested resource could not be found."
                        : "Access denied on permission '{$this->permission}'"
                ]);
        }

        $viewPath = $config->getErrorView($statusCode);

        return $response
            ->setStatusCode($statusCode)
            ->setBody(view($viewPath));
    }
}
