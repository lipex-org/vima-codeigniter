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

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class VimaResource implements RouteAttributeInterface
{
    /**
     * @param string $resource The resource class name (or model name)
     * @param string|int|null $idParam Name of the route segment parameter representing the resource ID (default 'id' or segment index)
     */
    public function __construct(
        private readonly string $resource,
        private readonly string|int|null $idParam = null,
        private readonly ?string $redirectPage = null
    ) {
    }

    public function before(RequestInterface $request): RequestInterface|ResponseInterface|null
    {
        $config = config('Vima');

        // 1. Resolve action name from the matched route / controller method
        $router = Services::router();
        $method = $router->methodName();

        // Map controller method names to Vima policy actions
        $actionMap = [
            'index' => 'viewAny',
            'show' => 'view',
            'create' => 'create',
            'new' => 'create',
            'store' => 'create',
            'edit' => 'edit',
            'update' => 'edit',
            'delete' => 'delete',
            'remove' => 'delete',
        ];

        $action = $actionMap[$method] ?? $method;

        // 2. Resolve the resource entity
        $resolvedResource = null;
        $idParam = $this->idParam ?? 'id';
        $id = $this->getParamValue($request, $idParam);

        if ($id) {
            $model = model($this->resource);
            if ($model) {
                $idResolver = $config->routeSegmentResolver ?? null;
                if ($idResolver && is_callable($idResolver)) {
                    $id = call_user_func($idResolver, $id);
                }
                $resolvedResource = $model->find($id);
            }
        }

        // If it's a global action like viewAny or create/new, the resource is the class string
        $checkResource = $resolvedResource ?? $this->resource;

        // 3. Run the authorization check
        $user = $config->getCurrentUser();

        if (!Vima::auth()->can($user, $action, $checkResource)) {
            if ($this->redirectPage) {
                return Services::response()->redirect(site_url($this->redirectPage));
            }

            if (stripos($request->getHeaderLine('Accept'), 'application/json') !== false || $request->isAJAX()) {
                return Services::response()
                    ->setStatusCode(403)
                    ->setJSON([
                        'error' => 'Access denied',
                        'message' => "Access denied on resource action '{$action}'"
                    ]);
            }

            $viewPath = $config->view403 ?? 'Vima\CodeIgniter\Views\error_403';
            return Services::response()
                ->setStatusCode(403)
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
