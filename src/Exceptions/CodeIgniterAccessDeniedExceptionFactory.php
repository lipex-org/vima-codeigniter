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

use Vima\Core\Exceptions\AccessDeniedExceptionFactoryInterface;
use Vima\Core\Exceptions\AccessDeniedExceptionInterface;

/**
 * Factory for creating CodeIgniter 4 specific AccessDeniedException.
 */
class CodeIgniterAccessDeniedExceptionFactory implements AccessDeniedExceptionFactoryInterface
{
    /**
     * Create the CodeIgniter 4 AccessDeniedException instance.
     */
    public function create(string $permission, mixed $user = null, mixed $userResolver = null): \Throwable&AccessDeniedExceptionInterface
    {
        $userId = null;
        if ($user !== null) {
            if ($userResolver !== null && method_exists($userResolver, 'resolveId')) {
                try {
                    $userId = (string) $userResolver->resolveId($user);
                } catch (\Throwable $e) {
                }
            } elseif (method_exists($user, 'vimaGetId')) {
                $userId = (string) $user->vimaGetId();
            } elseif (method_exists($user, 'getId')) {
                $userId = (string) $user->getId();
            } elseif (isset($user->id)) {
                $userId = (string) $user->id;
            }
        }

        return new AccessDeniedException($permission, $user, $userId);
    }
}
