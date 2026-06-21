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

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class VimaAuthorize implements RouteAttributeInterface
{
    public function __construct(
        private readonly string $permission,
        private readonly ?string $redirectPage = null
    ) {
    }

    public function before(RequestInterface $request): RequestInterface|ResponseInterface|null
    {
        $config = config('Vima');
        $resource = vima_context();

        if (!can($this->permission, $resource)) {
            if ($this->redirectPage) {
                return Services::response()->redirect(site_url($this->redirectPage));
            }

            // Check if request expects JSON
            if (stripos($request->getHeaderLine('Accept'), 'application/json') !== false || $request->isAJAX()) {
                return Services::response()
                    ->setStatusCode(403)
                    ->setJSON([
                        'error' => 'Access denied',
                        'message' => "Access denied on permission '{$this->permission}'"
                    ]);
            }

            $viewPath = $config->view403 ?? 'Vima\CodeIgniter\Views\error_403';
            return Services::response()
                ->setStatusCode(403)
                ->setBody(view($viewPath));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response): ?ResponseInterface
    {
        return null;
    }
}
