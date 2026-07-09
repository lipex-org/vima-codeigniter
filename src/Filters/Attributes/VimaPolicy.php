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

namespace Vima\CodeIgniter\Filters\Attributes;

use Attribute;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Router\Attributes\RouteAttributeInterface;
use Config\Services;
use Vima\Core\Vima;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class VimaPolicy implements RouteAttributeInterface
{
    /**
     * @param string $action The policy action/method to check (e.g. 'edit')
     * @param string|object $resource The resource class name (or resource object)
     * @param string|int|null $idParam Name of the route segment parameter representing the resource ID (e.g., 'id')
     */
    public function __construct(
        private readonly string $action,
        private readonly string|object $resource,
        private readonly string|int|null $idParam = null,
        private readonly ?string $redirectPage = null
    ) {
    }

    public function before(RequestInterface $request): RequestInterface|ResponseInterface|null
    {
        $config = config('Vima');
        
        // Resolve the resource if class name and ID parameter are provided
        $resolvedResource = null;
        if (is_string($this->resource) && $this->idParam !== null) {
            $id = $this->getParamValue($request, $this->idParam);
            if ($id) {
                $modelName = $this->resource;
                $model = model($modelName);
                if ($model) {
                    $idResolver = $config->routeSegmentResolver ?? null;
                    if ($idResolver && is_callable($idResolver)) {
                        $id = call_user_func($idResolver, $id);
                    }
                    $resolvedResource = $model->find($id);
                }
            }
        } else {
            $resolvedResource = is_object($this->resource) ? $this->resource : vima_context();
        }

        // Run the authorization check
        $user = $config->getCurrentUser();
        
        if (!Vima::auth()->can($user, $this->action, $resolvedResource)) {
            if ($this->redirectPage) {
                return Services::response()->redirect(site_url($this->redirectPage));
            }

            $statusCode = $config->getDenyStatusCode();
            $errorMsg = ($statusCode === 404) ? 'Resource not found' : 'Access denied';

            if (stripos($request->getHeaderLine('Accept'), 'application/json') !== false || $request->isAJAX()) {
                return Services::response()
                    ->setStatusCode($statusCode)
                    ->setJSON([
                        'error' => $errorMsg,
                        'message' => ($statusCode === 404) 
                            ? "The requested resource could not be found."
                            : "Access denied on policy action '{$this->action}'"
                    ]);
            }

            $viewPath = $config->getErrorView($statusCode);
            return Services::response()
                ->setStatusCode($statusCode)
                ->setBody(view($viewPath));
        }

        // Store the resolved resource in context so subsequent checks can use it
        if ($resolvedResource) {
            Services::vima_context()->set($resolvedResource);
        }

        return null;
    }

    private function getParamValue(RequestInterface $request, string|int $paramName): mixed
    {
        // Try to get from router
        $router = Services::router();
        $params = $router->getMatchedRouteOptions()['params'] ?? [];
        if (isset($params[$paramName])) {
            return $params[$paramName];
        }

        // Try getting from segments if integer
        if (is_int($paramName)) {
            return $request->getUri()->getSegment($paramName);
        }
        
        // Try parsing segments or query parameters
        $uri = $request->getUri();
        $segments = $uri->getSegments();
        foreach ($segments as $segment) {
            if (is_numeric($segment)) {
                return $segment; // common fallback for resource IDs
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response): ?ResponseInterface
    {
        return null;
    }
}
