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
use Config\Services;

/**
 * Filter to automatically load resources based on route segments
 * and make them available for ABAC checks.
 */
class VimaResourceFilter implements FilterInterface
{
    /**
     * @param array|null $arguments [modelName, segmentIndex]
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments)) {
            return;
        }

        $modelName = $arguments[0] ?? null;
        $segmentIndex = $arguments[1] ?? 1;
        $resolver = $arguments[2] ?? null;

        if (!$modelName) {
            return;
        }

        $id = $request->getUri()->getSegment($segmentIndex);

        if ($id) {
            $config = config('Vima');
            $model = model($modelName);

            // 1. If resolver is a method on the model
            if ($resolver && method_exists($model, $resolver)) {
                $resource = $model->$resolver($id);
            } else {
                // 2. Resolve ID if a global or local resolver is provided
                // This handles both strings (function names) and actual callables
                $idResolver = $resolver ?? $config->routeSegmentResolver ?? null;

                if ($idResolver && is_callable($idResolver)) {
                    $id = call_user_func($idResolver, $id);
                }

                $resource = $model->find($id);
            }

            if ($resource) {
                // Store in shared service container for later use in vima()->can()
                // or similar mechanisms
                Services::vima_context()->set($resource);
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
